<?php

namespace App\Services;

use App\Models\OfferCategory;
use App\Models\ScrapedOffer;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Récupère le flux Codeur et enregistre les offres.
 *
 * Partagé par la commande planifiée et par le bouton d'import manuel : la
 * logique ne doit exister qu'à un seul endroit, sans qu'un contrôleur ait à
 * appeler une commande artisan.
 */
class CodeurFeedImporter
{
    private const ETAG_CACHE_KEY = 'codeur.feed.etag';

    public function __construct(private readonly CodeurFeedParser $parser) {}

    /**
     * @param  bool  $force  Ignore l'ETag mémorisé et retélécharge le flux.
     */
    public function import(bool $force = false): CodeurImportResult
    {
        $url = config('services.codeur.feed_url');
        $response = $this->fetch($url, $force);

        if ($response === null) {
            return CodeurImportResult::failed(__('The Codeur feed is unreachable.'));
        }

        // Le flux honore les requêtes conditionnelles : un 304 signifie qu'il
        // n'a pas bougé depuis le dernier passage.
        if ($response->status() === 304) {
            return CodeurImportResult::unchanged();
        }

        $offers = $this->parser->parse($response->body());

        if ($offers === []) {
            Log::warning('Flux Codeur : aucune offre exploitable', ['url' => $url]);

            return CodeurImportResult::failed(__('No usable offer in the feed. Has its format changed?'));
        }

        $newCategories = OfferCategory::discover(
            array_merge(...array_column($offers, 'categories')) ?: []
        );

        // Normaliser la casse contre la taxonomie avant de stocker, sinon une
        // offre taguée « sécurité » serait introuvable quand « Sécurité » est
        // active (whereJsonContains compare avec la casse).
        $map = OfferCategory::canonicalMap();

        foreach ($offers as $i => $offer) {
            $offers[$i]['categories'] = OfferCategory::canonicalise($offer['categories'], $map);
        }

        [$created, $known] = $this->store($offers);

        if ($etag = $response->header('ETag')) {
            Cache::forever(self::ETAG_CACHE_KEY, $etag);
        }

        return new CodeurImportResult(
            success: true,
            received: count($offers),
            created: $created,
            known: $known,
            newCategories: $newCategories,
        );
    }

    private function fetch(string $url, bool $force): ?Response
    {
        $headers = [];

        if (! $force && $etag = Cache::get(self::ETAG_CACHE_KEY)) {
            $headers['If-None-Match'] = $etag;
        }

        try {
            return Http::withHeaders($headers)
                ->timeout(20)
                ->retry(2, 500, throw: false)
                ->get($url);
        } catch (\Throwable $e) {
            // Le flux est indisponible : on log et on sort proprement. La
            // commande repassera dans 20 minutes.
            Log::error('Flux Codeur injoignable', ['url' => $url, 'error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $offers
     * @return array{0: int, 1: int}
     */
    private function store(array $offers): array
    {
        $created = 0;
        $known = 0;

        foreach ($offers as $offer) {
            // Ne jamais écraser le statut d'une offre déjà triée à la main.
            if (ScrapedOffer::where('external_id', $offer['external_id'])->exists()) {
                $known++;

                continue;
            }

            ScrapedOffer::create($offer);
            $created++;
        }

        return [$created, $known];
    }
}

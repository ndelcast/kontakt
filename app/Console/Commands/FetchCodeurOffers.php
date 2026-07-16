<?php

namespace App\Console\Commands;

use App\Models\OfferCategory;
use App\Models\ScrapedOffer;
use App\Services\CodeurFeedParser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchCodeurOffers extends Command
{
    protected $signature = 'offers:fetch-codeur {--force : Ignorer l\'ETag mémorisé et retélécharger le flux}';

    protected $description = 'Récupère les offres publiées sur le flux RSS de Codeur';

    private const ETAG_CACHE_KEY = 'codeur.feed.etag';

    public function handle(CodeurFeedParser $parser): int
    {
        $url = config('services.codeur.feed_url');

        $response = $this->fetch($url);

        if ($response === null) {
            return self::FAILURE;
        }

        // Le flux honore les requêtes conditionnelles : un 304 signifie qu'il
        // n'a pas bougé depuis le dernier passage, il n'y a rien à faire.
        if ($response->status() === 304) {
            $this->info('Flux inchangé (304), rien à importer.');

            return self::SUCCESS;
        }

        $offers = $parser->parse($response->body());

        if ($offers === []) {
            $this->warn('Aucune offre exploitable dans le flux. Le format a-t-il changé ?');
            Log::warning('Flux Codeur : aucune offre exploitable', ['url' => $url]);

            return self::FAILURE;
        }

        $allCategories = array_merge(...array_column($offers, 'categories')) ?: [];
        $discovered = OfferCategory::discover($allCategories);

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

        $this->info(sprintf(
            'Reçues : %d — créées : %d — déjà connues : %d — nouvelles catégories : %d',
            count($offers),
            $created,
            $known,
            $discovered
        ));

        return self::SUCCESS;
    }

    private function fetch(string $url): ?\Illuminate\Http\Client\Response
    {
        $headers = [];

        if (! $this->option('force') && $etag = Cache::get(self::ETAG_CACHE_KEY)) {
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
            $this->error('Flux Codeur injoignable : '.$e->getMessage());
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
            $existing = ScrapedOffer::where('external_id', $offer['external_id'])->first();

            // Ne jamais écraser le statut d'une offre déjà triée à la main.
            if ($existing !== null) {
                $known++;

                continue;
            }

            ScrapedOffer::create($offer);
            $created++;
        }

        return [$created, $known];
    }
}

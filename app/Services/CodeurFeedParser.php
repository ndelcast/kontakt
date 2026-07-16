<?php

namespace App\Services;

use Illuminate\Support\Carbon;

/**
 * Analyse le flux RSS de Codeur (https://www.codeur.com/projects.rss).
 *
 * Le flux n'expose que title, link, guid, pubDate et description. Le budget et
 * les catégories ne sont pas structurés : ils sont noyés dans le premier <p> de
 * la description, sous la forme :
 *
 *     <p>Budget : Moins de 500 € - Catégories : Rédaction, Data mining</p>
 *
 * D'où l'extraction par expression régulière ci-dessous. Format vérifié stable
 * sur l'intégralité du flux.
 */
class CodeurFeedParser
{
    /**
     * Le budget est un ensemble fermé de paliers, pas du texte libre.
     * Un palier inconnu (si Codeur fait évoluer sa grille) conserve son
     * libellé brut et laisse les bornes nulles plutôt que d'échouer.
     */
    private const BUDGET_TIERS = [
        'Moins de 500 €' => [null, 500],
        '500 € à 1 000 €' => [500, 1000],
        '1 000 € à 10 000 €' => [1000, 10000],
        '10 000 € et plus' => [10000, null],
    ];

    /**
     * @return array<int, array<string, mixed>>
     */
    public function parse(string $xml): array
    {
        $offers = [];

        foreach ($this->extractItems($xml) as $item) {
            $offer = $this->parseItem($item);

            if ($offer !== null) {
                $offers[] = $offer;
            }
        }

        return $offers;
    }

    /**
     * @return array<int, string>
     */
    private function extractItems(string $xml): array
    {
        preg_match_all('/<item>(.*?)<\/item>/s', $xml, $matches);

        return $matches[1] ?? [];
    }

    /**
     * Une offre non exploitable renvoie null : elle est ignorée sans jamais
     * interrompre le traitement des autres.
     *
     * @return array<string, mixed>|null
     */
    private function parseItem(string $item): ?array
    {
        $externalId = $this->tag($item, 'guid');
        $title = $this->tag($item, 'title');
        $url = $this->tag($item, 'link');

        if ($externalId === null || $title === null || $url === null) {
            return null;
        }

        $rawDescription = $this->tag($item, 'description') ?? '';
        [$budgetRaw, $categories] = $this->extractBudgetAndCategories($rawDescription);
        [$budgetMin, $budgetMax] = $this->normaliseBudget($budgetRaw);

        return [
            'external_id' => $externalId,
            'title' => $title,
            'description' => $this->cleanDescription($rawDescription),
            'url' => $url,
            'budget_raw' => $budgetRaw,
            'budget_min' => $budgetMin,
            'budget_max' => $budgetMax,
            'categories' => $categories,
            'published_at' => $this->parseDate($this->tag($item, 'pubDate')),
        ];
    }

    private function tag(string $item, string $name): ?string
    {
        if (! preg_match('/<'.$name.'[^>]*>(.*?)<\/'.$name.'>/s', $item, $m)) {
            return null;
        }

        $value = $this->unwrapCdata($m[1]);
        $value = trim($this->decodeEntities($value));

        return $value === '' ? null : $value;
    }

    /**
     * Le flux double-encode ses entités : un titre arrive sous la forme
     * « Dashboard Client &amp;amp; Admin » ou « d&amp;#39;un ». Un seul décodage
     * laisserait « &amp; » et « &#39; » s'afficher littéralement à l'écran.
     *
     * On décode donc jusqu'à stabilisation, avec une borne : une chaîne
     * contenant volontairement le texte « &amp; » serait sur-décodée, ce qui
     * reste préférable à afficher « &#39; » à l'utilisateur.
     */
    private function decodeEntities(string $value): string
    {
        for ($i = 0; $i < 3; $i++) {
            $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            if ($decoded === $value) {
                break;
            }

            $value = $decoded;
        }

        return $value;
    }

    /**
     * La description arrive dans une section CDATA. Sans ce retrait explicite,
     * `strip_tags` avale bien le `<![CDATA[` d'ouverture (il ressemble à une
     * balise) mais laisse le `]]>` de fermeture en fin de texte.
     */
    private function unwrapCdata(string $value): string
    {
        if (preg_match('/^\s*<!\[CDATA\[(.*?)\]\]>\s*$/s', $value, $m)) {
            return $m[1];
        }

        return $value;
    }

    /**
     * @return array{0: string|null, 1: array<int, string>}
     */
    private function extractBudgetAndCategories(string $description): array
    {
        if (! preg_match('/Budget\s*:\s*(.*?)\s*-\s*Cat.gories\s*:\s*(.*?)<\/p>/su', $description, $m)) {
            return [null, []];
        }

        $budget = trim(strip_tags($m[1]));

        $categories = array_values(array_filter(array_map(
            fn (string $c): string => trim(strip_tags($c)),
            explode(',', $m[2])
        )));

        return [$budget === '' ? null : $budget, $categories];
    }

    /**
     * @return array{0: int|null, 1: int|null}
     */
    private function normaliseBudget(?string $budgetRaw): array
    {
        if ($budgetRaw === null) {
            return [null, null];
        }

        // Les espaces du flux sont parfois insécables : on normalise avant de
        // comparer, sinon aucun palier ne correspondrait jamais.
        $needle = $this->normaliseSpaces($budgetRaw);

        foreach (self::BUDGET_TIERS as $label => $bounds) {
            if ($this->normaliseSpaces($label) === $needle) {
                return $bounds;
            }
        }

        return [null, null];
    }

    private function normaliseSpaces(string $value): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', str_replace("\u{a0}", ' ', $value)));
    }

    /**
     * Retire la ligne budget/catégories, qui est déjà extraite dans ses propres
     * colonnes, puis réduit le HTML au texte. Le flux ne fournit de toute façon
     * qu'une accroche tronquée (~320 caractères), pas l'annonce intégrale.
     */
    private function cleanDescription(string $description): ?string
    {
        $withoutBudgetLine = preg_replace(
            '/<p>\s*Budget\s*:.*?<\/p>/su',
            '',
            $description,
            1
        ) ?? $description;

        $text = trim((string) preg_replace('/\s+/u', ' ', strip_tags($withoutBudgetLine)));

        return $text === '' ? null : $text;
    }

    private function parseDate(?string $value): ?Carbon
    {
        if ($value === null) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}

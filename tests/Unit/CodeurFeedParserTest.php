<?php

namespace Tests\Unit;

use App\Services\CodeurFeedParser;
use PHPUnit\Framework\TestCase;

class CodeurFeedParserTest extends TestCase
{
    private function parser(): CodeurFeedParser
    {
        return new CodeurFeedParser();
    }

    private function feed(string ...$items): string
    {
        return '<rss><channel>'.implode('', $items).'</channel></rss>';
    }

    private function item(string $guid, string $description): string
    {
        return <<<XML
        <item>
            <title>Automatisation N8N</title>
            <link>https://www.codeur.com/projects/{$guid}-automatisation-n8n</link>
            <guid>{$guid}</guid>
            <pubDate>Thu, 16 Jul 2026 17:15:33 +0200</pubDate>
            <description><![CDATA[{$description}]]></description>
        </item>
        XML;
    }

    public function test_it_extracts_the_core_fields_of_an_offer(): void
    {
        $offers = $this->parser()->parse($this->feed($this->item(
            '486686',
            '<p>Budget : Moins de 500 € - Catégories : Développement spécifique, No code</p> <p>Automatisation pour faciliter la création de dossiers.</p>'
        )));

        $this->assertCount(1, $offers);
        $this->assertSame('486686', $offers[0]['external_id']);
        $this->assertSame('Automatisation N8N', $offers[0]['title']);
        $this->assertSame('https://www.codeur.com/projects/486686-automatisation-n8n', $offers[0]['url']);
        $this->assertSame(['Développement spécifique', 'No code'], $offers[0]['categories']);
        $this->assertSame('2026-07-16 17:15:33', $offers[0]['published_at']->format('Y-m-d H:i:s'));
    }

    /**
     * Le budget est un ensemble fermé de paliers. Le flux utilise par endroits
     * des espaces insécables : sans normalisation, aucun palier ne
     * correspondrait et toutes les bornes resteraient nulles.
     */
    public function test_it_normalises_every_known_budget_tier(): void
    {
        $cases = [
            'Moins de 500 €' => [null, 500],
            '500 € à 1 000 €' => [500, 1000],
            '1 000 € à 10 000 €' => [1000, 10000],
            '10 000 € et plus' => [10000, null],
            // Variante à espaces insécables, telle que le flux la produit.
            "1 000 €\u{a0}à 10 000 €" => [1000, 10000],
        ];

        foreach ($cases as $raw => [$min, $max]) {
            $offers = $this->parser()->parse($this->feed($this->item(
                '1',
                "<p>Budget : {$raw} - Catégories : API</p>"
            )));

            $this->assertSame($min, $offers[0]['budget_min'], "min pour « {$raw} »");
            $this->assertSame($max, $offers[0]['budget_max'], "max pour « {$raw} »");
        }
    }

    /**
     * Si Codeur fait évoluer sa grille tarifaire, on conserve le libellé brut
     * plutôt que d'échouer ou d'inventer des bornes.
     */
    public function test_an_unknown_budget_tier_keeps_its_raw_label_without_bounds(): void
    {
        $offers = $this->parser()->parse($this->feed($this->item(
            '1',
            '<p>Budget : 50 000 € et plus - Catégories : API</p>'
        )));

        $this->assertSame('50 000 € et plus', $offers[0]['budget_raw']);
        $this->assertNull($offers[0]['budget_min']);
        $this->assertNull($offers[0]['budget_max']);
    }

    public function test_it_strips_the_budget_line_from_the_description(): void
    {
        $offers = $this->parser()->parse($this->feed($this->item(
            '1',
            '<p>Budget : Moins de 500 € - Catégories : API</p> <p>Le vrai texte de l\'annonce.</p>'
        )));

        $this->assertSame("Le vrai texte de l'annonce.", $offers[0]['description']);
        $this->assertStringNotContainsString('Budget :', (string) $offers[0]['description']);
    }

    /**
     * La description arrive en CDATA. strip_tags avale l'ouverture mais laisse
     * la fermeture : sans retrait explicite, toute description se terminait par
     * « ]]> ».
     */
    public function test_it_does_not_leak_cdata_markers_into_the_description(): void
    {
        $offers = $this->parser()->parse($this->feed($this->item(
            '1',
            '<p>Budget : Moins de 500 € - Catégories : API</p> <p>Le vrai texte.</p>'
        )));

        $this->assertStringNotContainsString(']]>', (string) $offers[0]['description']);
        $this->assertStringNotContainsString('CDATA', (string) $offers[0]['description']);
    }

    /**
     * Le flux double-encode ses entités. Sans décodage répété, les titres
     * s'affichaient « Réalisation d&#39;un Dashboard &amp; Admin ».
     */
    public function test_it_decodes_double_encoded_entities(): void
    {
        $xml = <<<'XML'
        <rss><channel><item>
            <title>Réalisation d&amp;#39;un Dashboard Client &amp;amp; Admin</title>
            <link>https://www.codeur.com/projects/1-x</link>
            <guid>1</guid>
            <pubDate>Thu, 16 Jul 2026 17:15:33 +0200</pubDate>
            <description><![CDATA[<p>Budget : Moins de 500 € - Catégories : API</p>]]></description>
        </item></channel></rss>
        XML;

        $offers = $this->parser()->parse($xml);

        $this->assertSame("Réalisation d'un Dashboard Client & Admin", $offers[0]['title']);
    }

    /**
     * Une offre illisible ne doit jamais interrompre le traitement des autres.
     */
    public function test_a_malformed_item_is_skipped_without_dropping_the_others(): void
    {
        $offers = $this->parser()->parse($this->feed(
            '<item><title>Sans guid ni lien</title></item>',
            $this->item('486686', '<p>Budget : Moins de 500 € - Catégories : API</p>')
        ));

        $this->assertCount(1, $offers);
        $this->assertSame('486686', $offers[0]['external_id']);
    }

    /**
     * Le format budget/catégories est stable, mais il vit dans du HTML libre :
     * s'il disparaît, l'offre reste exploitable sans ses bornes.
     */
    public function test_an_item_without_the_budget_line_is_still_usable(): void
    {
        $offers = $this->parser()->parse($this->feed($this->item(
            '486686',
            '<p>Une annonce sans la ligne habituelle.</p>'
        )));

        $this->assertCount(1, $offers);
        $this->assertNull($offers[0]['budget_raw']);
        $this->assertSame([], $offers[0]['categories']);
    }
}

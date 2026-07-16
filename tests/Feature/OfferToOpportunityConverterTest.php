<?php

namespace Tests\Feature;

use App\Enums\OfferStatus;
use App\Models\Opportunity;
use App\Models\PipelineStage;
use App\Models\ScrapedOffer;
use App\Models\Team;
use App\Services\OfferToOpportunityConverter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class OfferToOpportunityConverterTest extends TestCase
{
    use RefreshDatabase;

    private Team $team;

    protected function setUp(): void
    {
        parent::setUp();

        $this->team = Team::create(['name' => 'Jogr', 'slug' => 'jogr']);
    }

    private function converter(): OfferToOpportunityConverter
    {
        return app(OfferToOpportunityConverter::class);
    }

    private function stage(string $name, int $position, array $flags = []): PipelineStage
    {
        return PipelineStage::create([
            'team_id' => $this->team->id,
            'name' => $name,
            'slug' => strtolower($name),
            'position' => $position,
            ...$flags,
        ]);
    }

    private function offer(array $attributes = []): ScrapedOffer
    {
        return ScrapedOffer::create([
            'external_id' => '486686',
            'title' => 'Automatisation N8N',
            'description' => 'Automatisation pour faciliter la création de dossiers.',
            'url' => 'https://www.codeur.com/projects/486686-automatisation-n8n',
            'budget_raw' => '1 000 € à 10 000 €',
            'budget_min' => 1000,
            'budget_max' => 10000,
            'categories' => ['Développement spécifique', 'No code'],
            'published_at' => now(),
            'status' => OfferStatus::New,
            ...$attributes,
        ]);
    }

    public function test_it_creates_an_opportunity_in_the_first_open_stage(): void
    {
        $lead = $this->stage('Lead', 1);
        $this->stage('Qualified', 2);

        $opportunity = $this->converter()->convert($this->offer(), $this->team->id);

        $this->assertSame($lead->id, $opportunity->pipeline_stage_id);
        $this->assertSame('Automatisation N8N', $opportunity->name);
        $this->assertSame($this->team->id, $opportunity->team_id);
    }

    /**
     * Le plancher de la fourchette sert de valeur : c'est le seul montant dont
     * on soit certain.
     */
    public function test_it_uses_the_budget_floor_as_the_opportunity_value(): void
    {
        $this->stage('Lead', 1);

        $opportunity = $this->converter()->convert($this->offer(), $this->team->id);

        $this->assertSame('1000.00', $opportunity->value);
    }

    /**
     * « Moins de 500 € » n'a pas de plancher : mieux vaut zéro qu'un montant
     * inventé qui fausserait les prévisions.
     */
    public function test_an_offer_without_a_budget_floor_gets_a_zero_value(): void
    {
        $this->stage('Lead', 1);

        $opportunity = $this->converter()->convert(
            $this->offer(['budget_raw' => 'Moins de 500 €', 'budget_min' => null, 'budget_max' => 500]),
            $this->team->id
        );

        $this->assertSame('0.00', $opportunity->value);
        $this->assertStringContainsString('Moins de 500 €', $opportunity->notes);
    }

    public function test_it_keeps_the_offer_url_and_budget_in_the_notes(): void
    {
        $this->stage('Lead', 1);

        $opportunity = $this->converter()->convert($this->offer(), $this->team->id);

        $this->assertStringContainsString('https://www.codeur.com/projects/486686', $opportunity->notes);
        $this->assertStringContainsString('1 000 € à 10 000 €', $opportunity->notes);
    }

    public function test_it_links_the_offer_and_the_opportunity_both_ways(): void
    {
        $this->stage('Lead', 1);
        $offer = $this->offer();

        $opportunity = $this->converter()->convert($offer, $this->team->id);

        $this->assertSame($offer->id, $opportunity->origin_offer_id);
        $this->assertSame($opportunity->id, $offer->fresh()->converted_opportunity_id);
        $this->assertSame(OfferStatus::Converted, $offer->fresh()->status);
    }

    /**
     * Rien ne garantit que les étapes gagnée et perdue soient en fin de
     * pipeline : y déposer une offre fraîche fausserait les statistiques dès la
     * conversion.
     */
    public function test_it_never_converts_into_a_won_or_lost_stage(): void
    {
        $this->stage('Won', 1, ['is_won' => true]);
        $this->stage('Lost', 2, ['is_lost' => true]);
        $lead = $this->stage('Lead', 3);

        $opportunity = $this->converter()->convert($this->offer(), $this->team->id);

        $this->assertSame($lead->id, $opportunity->pipeline_stage_id);
    }

    public function test_it_refuses_to_convert_the_same_offer_twice(): void
    {
        $this->stage('Lead', 1);
        $offer = $this->offer();
        $this->converter()->convert($offer, $this->team->id);

        $this->expectException(RuntimeException::class);

        $this->converter()->convert($offer->fresh(), $this->team->id);
    }

    public function test_it_fails_cleanly_when_the_team_has_no_open_stage(): void
    {
        $this->stage('Won', 1, ['is_won' => true]);

        $this->expectException(RuntimeException::class);

        try {
            $this->converter()->convert($this->offer(), $this->team->id);
        } catch (RuntimeException $e) {
            // L'offre ne doit pas être marquée convertie si rien n'a été créé.
            $this->assertSame(0, Opportunity::count());
            $this->assertSame(OfferStatus::New, ScrapedOffer::first()->status);

            throw $e;
        }
    }
}

<?php

namespace App\Services;

use App\Enums\OfferStatus;
use App\Models\Opportunity;
use App\Models\PipelineStage;
use App\Models\ScrapedOffer;
use RuntimeException;
use Illuminate\Support\Facades\DB;

/**
 * Transforme une offre du marché en opportunité du pipeline.
 *
 * Isolé du contrôleur pour rester greffable : le brief prévoit qu'un connecteur
 * « pousser vers un outil externe » puisse exister un jour à côté de celui-ci.
 */
class OfferToOpportunityConverter
{
    public function convert(ScrapedOffer $offer, int $teamId): Opportunity
    {
        if ($offer->converted_opportunity_id !== null) {
            throw new RuntimeException(__('This offer has already been sent to the pipeline.'));
        }

        $stage = $this->firstOpenStage($teamId);

        if ($stage === null) {
            throw new RuntimeException(__('This team has no pipeline stage to receive the offer.'));
        }

        return DB::transaction(function () use ($offer, $teamId, $stage): Opportunity {
            $opportunity = Opportunity::create([
                'team_id' => $teamId,
                'pipeline_stage_id' => $stage->id,
                'origin_offer_id' => $offer->id,
                'name' => $offer->title,
                'value' => $offer->budget_min ?? 0,
                'notes' => $this->notes($offer),
                'started_at' => now(),
            ]);

            $offer->update([
                'status' => OfferStatus::Converted,
                'converted_opportunity_id' => $opportunity->id,
            ]);

            return $opportunity;
        });
    }

    /**
     * La première étape *ouverte* du pipeline, par position croissante.
     *
     * Les étapes gagnée et perdue sont écartées : rien ne garantit qu'elles
     * soient en fin de pipeline, et y déposer une offre fraîche fausserait les
     * statistiques dès la conversion.
     */
    private function firstOpenStage(int $teamId): ?PipelineStage
    {
        return PipelineStage::where('team_id', $teamId)
            ->where('is_won', false)
            ->where('is_lost', false)
            ->orderBy('position')
            ->first();
    }

    /**
     * Le budget est une fourchette là où l'opportunité n'a qu'un montant : on
     * retient le plancher comme valeur (voir `value` ci-dessus) et on conserve
     * le libellé d'origine ici, pour que rien ne se perde.
     */
    private function notes(ScrapedOffer $offer): string
    {
        $lines = [
            __('Offer from Codeur.com'),
            $offer->url,
            '',
            __('Budget').' : '.($offer->budget_raw ?? '—'),
        ];

        if ($offer->categories !== []) {
            $lines[] = __('Categories').' : '.implode(', ', $offer->categories);
        }

        if ($offer->published_at !== null) {
            $lines[] = __('Published on').' : '.$offer->published_at->format('d/m/Y H:i');
        }

        if ($offer->description !== null) {
            $lines[] = '';
            $lines[] = $offer->description;
        }

        return implode("\n", $lines);
    }
}

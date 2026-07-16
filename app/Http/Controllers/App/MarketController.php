<?php

namespace App\Http\Controllers\App;

use App\Enums\OfferStatus;
use App\Http\Controllers\Controller;
use App\Models\OfferCategory;
use App\Models\ScrapedOffer;
use App\Services\OfferToOpportunityConverter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

/**
 * Le Marché : les offres de missions collectées sur Codeur.
 *
 * Ces données sont globales et non cloisonnées par équipe — voir
 * docs/BRIEF-kontak-offres-codeur.md. Aucun filtrage par `team_id` ici, c'est
 * volontaire.
 */
class MarketController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'status' => ['nullable', new Enum(OfferStatus::class)],
            'category' => ['nullable', 'string'],
            'budget_min' => ['nullable', 'integer', 'min:0'],
            'search' => ['nullable', 'string', 'max:255'],
            'all_categories' => ['nullable', 'boolean'],
        ]);

        $showAll = (bool) ($filters['all_categories'] ?? false);

        $offers = ScrapedOffer::query()
            ->unless($showAll, fn (Builder $q) => $q->inActiveCategories())
            ->when(
                $filters['status'] ?? null,
                fn (Builder $q, string $status) => $q->where('status', $status),
                // Par défaut on masque le bruit déjà écarté : une inbox sert à
                // traiter ce qui reste, pas à contempler ce qu'on a rejeté.
                fn (Builder $q) => $q->whereNot('status', OfferStatus::Ignored->value)
            )
            ->when(
                $filters['category'] ?? null,
                fn (Builder $q, string $c) => $q->whereJsonContains('categories', $c)
            )
            ->when(
                $filters['budget_min'] ?? null,
                fn (Builder $q, int $min) => $q->where(function (Builder $q) use ($min) {
                    // Un palier ouvert vers le haut (« 10 000 € et plus ») a un
                    // budget_max nul : il satisfait tout plancher.
                    $q->whereNull('budget_max')->orWhere('budget_max', '>=', $min);
                })
            )
            ->when(
                $filters['search'] ?? null,
                fn (Builder $q, string $s) => $q->where(function (Builder $q) use ($s) {
                    $q->where('title', 'like', "%{$s}%")
                        ->orWhere('description', 'like', "%{$s}%");
                })
            )
            ->orderByDesc('published_at')
            ->paginate(25)
            ->withQueryString()
            ->through(fn (ScrapedOffer $o): array => [
                'id' => $o->id,
                'title' => $o->title,
                'description' => $o->description,
                'url' => $o->url,
                'budget_raw' => $o->budget_raw,
                'categories' => $o->categories,
                'published_at' => $o->published_at?->toIso8601String(),
                'status' => $o->status->value,
                'status_label' => $o->status->getLabel(),
                'status_color' => $o->status->getColor(),
                'converted_opportunity_id' => $o->converted_opportunity_id,
            ]);

        return Inertia::render('Market/Index', [
            'offers' => $offers,
            'filters' => $filters,
            'categories' => OfferCategory::orderBy('name')->pluck('name'),
            'statuses' => collect(OfferStatus::cases())->map(fn (OfferStatus $s): array => [
                'value' => $s->value,
                'label' => $s->getLabel(),
            ]),
            'activeCategoryCount' => OfferCategory::where('is_active', true)->count(),
        ]);
    }

    public function updateStatus(Request $request, ScrapedOffer $offer): RedirectResponse
    {
        $validated = $request->validate([
            // On ne passe pas une offre en « converted » à la main : ce statut
            // n'a de sens qu'accompagné d'une opportunité liée.
            'status' => ['required', Rule::in([
                OfferStatus::New->value,
                OfferStatus::Seen->value,
                OfferStatus::Hot->value,
                OfferStatus::Ignored->value,
            ])],
        ]);

        $offer->update(['status' => $validated['status']]);

        return back();
    }

    /**
     * Envoie une offre dans le pipeline sous forme d'opportunité.
     *
     * L'opportunité créée est, elle, bien cloisonnée par équipe : seul le pool
     * d'offres est global.
     */
    public function convert(ScrapedOffer $offer, OfferToOpportunityConverter $converter): RedirectResponse
    {
        try {
            $opportunity = $converter->convert($offer, Auth::user()->current_team_id);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('app.opportunities.edit', $opportunity)
            ->with('success', __('Offer sent to the pipeline.'));
    }

    public function categories(): Response
    {
        // Le compteur est ce qui rend cet écran utile : il montre ce qu'activer
        // une catégorie ferait apparaître, plutôt que de cocher à l'aveugle.
        $categories = OfferCategory::orderBy('name')->get()->map(fn (OfferCategory $c): array => [
            'id' => $c->id,
            'name' => $c->name,
            'is_active' => $c->is_active,
            'offers_count' => ScrapedOffer::whereJsonContains('categories', $c->name)->count(),
        ]);

        return Inertia::render('Market/Categories', [
            'categories' => $categories,
        ]);
    }

    public function updateCategories(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'active' => ['present', 'array'],
            'active.*' => ['integer', 'exists:offer_categories,id'],
        ]);

        OfferCategory::query()->update(['is_active' => false]);
        OfferCategory::whereIn('id', $validated['active'])->update(['is_active' => true]);

        return back()->with('success', __('Categories updated.'));
    }
}

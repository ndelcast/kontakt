<?php

namespace App\Models;

use App\Enums\OfferStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Une offre de mission collectée sur le flux RSS de Codeur.
 *
 * Volontairement sans team_id ni trait BelongsToTenant : ce sont des données
 * publiques globales, identiques pour tout le monde, qu'aucune équipe ne
 * possède. Dérogation assumée à la convention multi-tenant du projet, décrite
 * dans docs/BRIEF-kontak-offres-codeur.md. Ne pas « corriger ».
 */
class ScrapedOffer extends Model
{
    protected $fillable = [
        'external_id',
        'title',
        'description',
        'url',
        'budget_raw',
        'budget_min',
        'budget_max',
        'categories',
        'published_at',
        'status',
        'converted_opportunity_id',
    ];

    protected $casts = [
        'categories' => 'array',
        'published_at' => 'datetime',
        'budget_min' => 'integer',
        'budget_max' => 'integer',
        'status' => OfferStatus::class,
    ];

    public function convertedOpportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class, 'converted_opportunity_id');
    }

    /**
     * Restreint aux offres portant au moins une catégorie active.
     *
     * Le filtre est appliqué à l'affichage, jamais à l'ingestion : on stocke
     * tout, afin qu'activer une catégorie révèle immédiatement l'historique.
     */
    public function scopeInActiveCategories(Builder $query): Builder
    {
        $active = OfferCategory::activeNames();

        // Aucune catégorie active signifie « aucune offre ne m'intéresse », et
        // non « montre-moi tout » : sans cela l'écran de configuration
        // afficherait zéro case cochée pendant que la liste déborde.
        if ($active === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $q) use ($active) {
            foreach ($active as $name) {
                $q->orWhereJsonContains('categories', $name);
            }
        });
    }
}

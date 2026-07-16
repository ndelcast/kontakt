<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Taxonomie des catégories publiées par Codeur. Volontairement sans team_id ni
 * trait BelongsToTenant : la taxonomie appartient à Codeur, pas à une équipe.
 */
class OfferCategory extends Model
{
    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Enregistre les catégories encore inconnues. Inactives par défaut : elles
     * apparaissent dans l'écran de configuration avec leur compteur, à
     * l'utilisateur de décider. Rien n'est perdu, rien n'apparaît sans décision.
     *
     * @param  array<int, string>  $names
     * @return int Nombre de catégories nouvellement découvertes.
     */
    public static function discover(array $names): int
    {
        $names = array_filter(array_map('trim', $names));

        if ($names === []) {
            return 0;
        }

        // La comparaison doit se faire dans le même référentiel que l'index
        // unique, que la collation MySQL rend insensible à la casse. Un
        // array_diff() en PHP, lui, est sensible à la casse : il jugerait
        // « sécurité » inconnu alors que « Sécurité » existe, et l'insertion
        // violerait la contrainte d'unicité.
        $known = array_map('mb_strtolower', static::query()->pluck('name')->all());
        $created = 0;

        foreach ($names as $name) {
            $lower = mb_strtolower($name);

            if (in_array($lower, $known, true)) {
                continue;
            }

            static::create(['name' => $name, 'is_active' => false]);
            $known[] = $lower;
            $created++;
        }

        return $created;
    }

    /**
     * @return array<int, string>
     */
    public static function activeNames(): array
    {
        return static::query()->where('is_active', true)->pluck('name')->all();
    }

    /**
     * Table de correspondance « nom en minuscules » => « nom canonique ».
     *
     * Codeur publie deux variantes de casse pour une même catégorie (« Sécurité »
     * et « sécurité »). La collation MySQL étant insensible à la casse, elles ne
     * forment qu'une ligne ici — mais `whereJsonContains`, lui, compare *avec* la
     * casse. Sans normalisation du JSON stocké, une offre taguée « sécurité »
     * resterait introuvable alors que « Sécurité » est active.
     *
     * @return array<string, string>
     */
    public static function canonicalMap(): array
    {
        return static::query()
            ->pluck('name')
            ->mapWithKeys(fn (string $name): array => [mb_strtolower($name) => $name])
            ->all();
    }

    /**
     * Ramène des noms bruts issus du flux à leur forme canonique en base.
     *
     * @param  array<int, string>  $names
     * @param  array<string, string>  $map
     * @return array<int, string>
     */
    public static function canonicalise(array $names, array $map): array
    {
        $canonical = array_map(
            fn (string $name): string => $map[mb_strtolower($name)] ?? $name,
            $names
        );

        return array_values(array_unique($canonical));
    }
}

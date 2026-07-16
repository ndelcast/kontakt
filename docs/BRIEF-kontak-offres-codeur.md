# Brief — Kontak : agrégation des offres Codeur.com

## Besoin

Faire tomber les offres de missions publiées sur Codeur.com directement dans Kontak, pour que Pierre-Arnaud et moi puissions les trier et convertir les plus intéressantes en opportunités du pipeline.

Périmètre volontairement resserré : **une seule source (Codeur), pas de scoring IA, pas de dédup cross-plateformes, pas de statistiques**. Le tri repose sur un **filtre par catégories, configurable depuis l'UI**.

## Décisions d'architecture actées

**Kontak interroge le flux RSS lui-même.** Pas de n8n, pas d'endpoint d'ingestion, pas de token API. Une commande artisan planifiée suffit, et ça évite d'installer une couche API, absente du projet.

**Les offres atterrissent dans une section « Offres » séparée**, pas directement dans le kanban. Table dédiée, page liste type inbox, et un bouton de conversion vers le pipeline. On évite ainsi de polluer les statistiques du pipeline avec des offres non qualifiées.

**On stocke toutes les offres, et on filtre à l'affichage.** C'est la conséquence directe du choix de configurer les catégories depuis l'UI (voir la section dédiée). Le filtre n'intervient jamais à l'ingestion.

**On branche depuis `main`.** La PR #14 y est mergée, donc `main` est désormais aligné sur ce qui tourne en production : Filament entièrement supprimé, authentification Inertia sur `/login`, et toute l'infrastructure nécessaire (pages Vue, `AppLayout.vue`, contrôleurs sous `App\Http\Controllers\App`). Plus de divergence, plus de précaution de déploiement à prendre.

**Le scoring IA est reporté**, pas abandonné. La configuration des catégories par l'UI permettra de constater à l'usage si l'inbox est déjà assez propre. Si elle l'est, l'IA n'aura pas lieu d'être ; sinon on l'ajoutera en connaissance de cause, sur des données réelles. En attendant, ce report économise le SDK Anthropic, la clé API, le job asynchrone et les colonnes de score.

## Le flux Codeur : constats vérifiés

Tout ce qui suit a été vérifié sur le flux réel, pas supposé.

**URL** : `https://www.codeur.com/projects.rss` (200, `application/rss+xml`).

**Balises disponibles** : `title`, `link`, `guid`, `pubDate`, `description`. C'est tout.

**La description est tronquée.** Elle mesure 328 caractères en médiane et 415 au maximum : c'est une accroche, pas l'annonce intégrale. Récupérer le texte complet obligerait à aller chercher la page du projet, donc à faire du scraping HTML — hors périmètre. Le tri se fera donc sur le titre, ces ~320 caractères, le palier de budget et les catégories, avec un lien vers l'annonce pour le détail.

**Le budget et les catégories ne sont pas structurés.** Ils sont noyés dans le premier `<p>` de la description, sous la forme :

```
<p>Budget : Moins de 500 € - Catégories : Rédaction, Data mining, Analyse big data</p>
```

Il faut donc les extraire par expression régulière. Testé sur l'intégralité du flux : 35 items sur 35 sont parsables, le format est stable.

**Le budget est un ensemble fermé de 4 paliers**, pas du texte libre :

| Valeur brute | `budget_min` | `budget_max` |
|---|---|---|
| `Moins de 500 €` | null | 500 |
| `500 € à 1 000 €` | 500 | 1000 |
| `1 000 € à 10 000 €` | 1000 | 10000 |
| `10 000 € et plus` | 10000 | null |

La normalisation est donc une simple table de correspondance. Prévoir malgré tout le cas d'un palier inconnu (conserver `budget_raw`, laisser les bornes à null) au cas où Codeur ferait évoluer sa grille.

**73 catégories distinctes** relevées sur l'échantillon (API, AWS, WordPress, Application mobile, Machine Learning, Rédaction…).

**Le `guid` est un identifiant numérique propre** (ex. `486685`), qui correspond au segment de l'URL. Il sert d'`external_id`.

**Fenêtre glissante de 35 items**, avec des rafales. La cadence moyenne est trompeuse : le 15 juillet, 8 offres sont tombées en 2 minutes (soit ~3,6/minute, avec des `guid` publiés à la seconde identique, vraisemblablement des posts automatisés). À ce rythme, la fenêtre se vide en une dizaine de minutes. **D'où un polling toutes les 20 minutes**, et non horaire.

**Le flux honore les requêtes conditionnelles** : il expose un ETag et répond `304` sur `If-None-Match`. Stocker l'ETag entre deux passages rend le polling quasi gratuit.

**Le flux n'est pas filtré.** Il déverse toutes les catégories confondues, à 15-30 offres/jour, dont beaucoup de bruit sans rapport avec le positionnement Jogr. **Le filtrage par catégorie est le mécanisme central de ce projet, pas un raffinement optionnel.**

## Le filtre catégories, configurable depuis l'UI

### Pourquoi on stocke tout

Filtrer à l'ingestion et configurer les catégories depuis l'UI sont incompatibles. Une offre écartée à l'ingestion n'existe pas : cocher une nouvelle catégorie n'afficherait rien tant que de nouvelles offres ne tombent pas, et la liste des catégories serait indécouvrable puisqu'on aurait jeté toutes les offres qui les portent.

On stocke donc **toutes** les offres et le filtre n'agit qu'à l'affichage. Le volume rend ce choix indolore : à 30 offres/jour, c'est environ 11 000 lignes et une dizaine de mégaoctets par an. En échange, cocher une catégorie révèle immédiatement tout l'historique, et la liste s'alimente d'elle-même.

### Écran de configuration

Le projet a déjà un précédent exact : les étapes de pipeline sont une liste gérée par l'utilisateur, avec son CRUD et son entrée de menu (`Pipeline > Étapes`). Les catégories suivent le même motif, sous `Offres > Catégories`.

L'écran liste les catégories connues avec, pour chacune, une case à cocher et **le nombre d'offres reçues**. Ce compteur est ce qui rend l'écran utile : on voit exactement ce qu'activer une catégorie ferait apparaître, plutôt que de cocher à l'aveugle.

**`offer_categories`**

- `name` (le libellé exact du flux)
- `is_active` (booléen)
- Unicité sur `(name)`

Le seed pré-remplit les 73 catégories relevées, pour que l'écran soit utile dès le premier jour plutôt qu'après une semaine de collecte. Une catégorie inconnue rencontrée à l'ingestion est créée automatiquement, **inactive par défaut** : elle apparaît dans l'écran avec son compteur, à vous de l'activer. Rien n'est jamais perdu, mais rien n'apparaît sans décision.

## Modèle de données

**`scraped_offers`**

- `external_id` — le `guid` du flux
- `title`
- `description` — texte nettoyé, hors ligne budget/catégories
- `url`
- `budget_raw` (string), `budget_min`, `budget_max` (nullable)
- `categories` (JSON array)
- `published_at`
- `status` : `new` → `seen` | `hot` | `ignored` | `converted`
- `converted_opportunity_id` (FK nullable vers `opportunities`)
- Unicité sur `(external_id)` — une seule source, inutile de composer avec un `source_id`

### Pas de `team_id` : dérogation assumée

`scraped_offers` et `offer_categories` sont les **seules tables du projet sans `team_id`**, et **ne doivent pas utiliser le trait `BelongsToTenant`**. C'est délibéré, et il faut le documenter pour qu'un lecteur futur ne « corrige » pas cette anomalie apparente.

La raison : les offres Codeur sont des données publiques globales. Le flux est identique pour tout le monde, aucune équipe ne les possède. Et la fonctionnalité est jogr-spécifique par construction — elle agrège des missions correspondant à un positionnement précis, dont aucune autre équipe de Kontak n'a l'usage. Une seule équipe triera ces offres : le `team_id` porterait la même valeur sur les 11 000 lignes annuelles, pour rien.

Bénéfice secondaire : le trait `BelongsToTenant` déduit le `team_id` du contexte de la requête. Dans une commande artisan planifiée, il n'y a ni utilisateur authentifié ni contexte de panel. Ne pas porter de `team_id` supprime le problème au lieu de le contourner.

**La conversion vers le pipeline reste normalement multi-tenant.** Elle a lieu dans un contrôleur, avec un utilisateur authentifié : l'`Opportunity` créée est cloisonnée comme n'importe quelle autre. Seul le pool d'offres est global.

Sur le `team_id` de l'`Opportunity` créée, suivre la convention des contrôleurs Inertia existants, qui le posent explicitement plutôt que de s'en remettre à l'auto-remplissage du trait :

```php
$validated['team_id'] = Auth::user()->current_team_id;
```

Depuis le merge de la PR #14, `BelongsToTenant` lit bien `auth()->user()?->current_team_id` et le remplirait donc tout seul. L'écriture explicite reste néanmoins préférable, par cohérence avec les six contrôleurs qui procèdent déjà ainsi.

Le jour où une deuxième équipe voudrait son propre tri, le modèle propre serait un pool global inchangé plus une table pivot portant le statut par équipe. C'est une migration à faire à ce moment-là, pas maintenant.

Pas de colonnes de score : on ne crée pas de champs pour une fonctionnalité qu'on ne construit pas. Elles arriveront avec le scoring, s'il arrive.

Pas de table `sources` : elle n'aurait qu'une ligne. L'URL du flux va en configuration. Si une deuxième plateforme arrive un jour, on introduira la table à ce moment-là.

Pas de `fingerprint` ni de `duplicate_of_id` : la déduplication cross-plateformes n'a pas de sens avec une source unique. L'unicité sur `external_id` suffit.

**Sur `opportunities`** : ajouter `origin_offer_id` (FK nullable) pour la traçabilité inverse.

## Ingestion

Une commande artisan (ex. `offers:fetch-codeur`), planifiée via `everyTwentyMinutes()` dans `routes/console.php`.

Déroulé :

1. GET conditionnel sur le flux avec l'ETag mémorisé. Si `304`, on s'arrête là.
2. Parsing des items : `guid`, `title`, `link`, `pubDate`, puis extraction budget/catégories depuis la description par regex.
3. Enregistrement des catégories inconnues, inactives par défaut.
4. Upsert sur `external_id` : création si nouvelle, **sans jamais écraser le `status`** d'une offre déjà triée. Aucun filtrage à ce stade.
5. Log du bilan : reçues / créées / déjà connues / nouvelles catégories découvertes.

En cas d'échec (flux indisponible, HTML modifié), la commande log l'erreur et sort proprement. Une offre non parsable ne doit jamais interrompre le traitement des autres.

**Point d'infrastructure** : `compose.yaml` n'a aujourd'hui **ni scheduler ni worker**. Il faudra ajouter un conteneur exécutant `schedule:run`, sinon la commande ne partira jamais en production. C'est une modification de déploiement, à ne pas oublier.

## Section « Offres »

Nouvelle entrée de navigation, en Inertia/Vue + PrimeVue, cohérente avec l'existant.

- **Vue liste type inbox**, triée par `published_at` desc, **restreinte par défaut aux catégories actives**
- Chaque ligne : titre, budget, catégories, âge de l'annonce, statut
- **Actions rapides** sans quitter la liste : vu / ignorer / 🔥 chaud
- **Vue détail** : description, lien vers l'annonce, bouton « Envoyer au pipeline »
- **Filtres** : statut, catégorie, budget, recherche texte
- Prévoir une bascule « afficher toutes les catégories », qui donne accès au stock complet sans passer par l'écran de configuration — utile pour vérifier ce que le filtre écarte

## Conversion vers le pipeline

Action « Envoyer au pipeline », disponible sur toute offre :

1. Crée une `Opportunity` dans la **première `PipelineStage`** de l'équipe (celle de plus petit `position`), pré-remplie avec le titre, le budget et l'URL de l'offre en notes
2. Passe l'offre en `converted` et lie `converted_opportunity_id` ↔ `origin_offer_id`
3. Pas de rapprochement automatique avec une `Company` ou un `Contact` : hors périmètre

## Phasage

**Phase 1 — Ingestion**
Migrations `scraped_offers` et `offer_categories`, seed des 73 catégories, commande de fetch avec parsing et upsert, planification à 20 minutes, conteneur scheduler. Tests sur le parsing (budget, catégories, item malformé), l'upsert (pas d'écrasement de statut) et la découverte de catégories inconnues.

**Phase 2 — Section Offres et configuration**
Liste, détail, actions de statut, filtres, et l'écran de configuration des catégories avec ses compteurs. À la fin de cette phase l'outil est complet et utilisable à deux.

**Phase 3 — Conversion**
Champ `origin_offer_id`, bouton d'envoi au pipeline, traçabilité.

**Plus tard, si le besoin se confirme — Scoring IA**
À rouvrir seulement si l'usage montre que le filtre catégories ne suffit pas. On disposera alors de plusieurs semaines d'offres réelles pour juger, et pour calibrer le prompt sur des cas concrets plutôt que sur des hypothèses.

## Contraintes

- Ne rien casser : contacts, companies et kanban actuels doivent fonctionner à l'identique
- Respecter les conventions du projet : contrôleurs sous `App\Http\Controllers\App`, pages sous `resources/js/Pages`
- **Exception documentée** : `scraped_offers` et `offer_categories` n'utilisent pas `BelongsToTenant` et n'ont pas de `team_id` (voir la section dédiée). Tout le reste du projet, y compris les `Opportunity` créées par conversion, reste multi-tenant à l'identique.
- Migrations réversibles
- Chaque phase se termine par un récap court, et ne démarre pas sans accord

## Questions ouvertes à trancher avant la Phase 1

**1. Qui accède à la section Offres ?** Puisque les offres sont globales, rien ne restreint plus leur visibilité par construction — c'est désormais une question d'autorisation, pas de tenancy. Or Kontak est réellement multi-équipes : `RegisterController` exige un `team_name` et crée une équipe à toute inscription hors invitation. Pierre-Arnaud, invité, rejoindra bien mon équipe, donc ce cas est réglé. Mais tout utilisateur approuvé hors invitation créerait sa propre équipe et verrait la section Offres si rien ne la garde. Trois options, de la plus simple à la plus stricte :

- ne rien garder, si toutes les équipes de cette instance sont les miennes ;
- une valeur de configuration désignant la ou les équipes autorisées ;
- une restriction aux `super_admin`, ce qui obligerait à élever Pierre-Arnaud et paraît disproportionné.

*La base de départ est tranchée : on branche depuis `main`, désormais aligné sur la production.*

*Deux questions se sont dissoutes en cours de cadrage. La liste des catégories retenues n'est plus bloquante : elle se règle depuis l'UI après déploiement, compteurs sous les yeux. Et le `team_id` des offres n'existe plus : il n'avait pas de raison d'être.*

## Note sur les tests

Le projet n'a aucun test aujourd'hui, hormis les deux exemples par défaut de Laravel. Les tests de la Phase 1 seront donc les premiers du projet, et poseront les conventions.

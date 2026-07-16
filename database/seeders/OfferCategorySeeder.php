<?php

namespace Database\Seeders;

use App\Models\OfferCategory;
use Illuminate\Database\Seeder;

class OfferCategorySeeder extends Seeder
{
    /**
     * Les catégories publiées par Codeur, relevées sur son flux RSS.
     *
     * Elles sont activées par défaut : mieux vaut commencer large et resserrer à
     * l'usage, une catégorie oubliée étant une offre invisible alors qu'une
     * catégorie de trop n'est qu'une ligne à ignorer. Les catégories découvertes
     * plus tard à l'ingestion arrivent en revanche inactives (voir
     * OfferCategory::discover).
     *
     * @var array<int, string>
     */
    private const CATEGORIES = [
        'API',
        'AWS',
        'Admin système',
        'Administration',
        'Analyse big data',
        'Android',
        'Animation 3D',
        'Application mobile',
        'Audio',
        'Base de données',
        'Big Data',
        'C',
        'C++',
        'CAO',
        'CMS',
        'CRM',
        'CSS',
        'ChatGPT',
        'Chatbot',
        'Comptabilité',
        'Création de site internet',
        'Data mining',
        'Développement spécifique',
        'E-learning',
        'ERP',
        'Experience utilisateur',
        'Full-stack',
        'Gestion site web',
        'Google Ads',
        'HTML',
        'Infogérance',
        'Infrastructure et réseaux',
        'Installation de Script',
        'IoT',
        'Jeux vidéo',
        'Landing page',
        'Linux',
        'Logiciel',
        'Lua',
        'Machine Learning',
        'Maintenance',
        'Marketing',
        'Migration',
        'Migration ou refonte de site',
        'Modules et composants',
        'Motion design',
        'Multimedia',
        'No code',
        'Prestashop',
        'Prospection commerciale',
        'R',
        'Ressources humaines',
        'Rédaction',
        'Référencement',
        'SEM',
        'SEO / GEO',
        'SaaS',
        'Shopify',
        'Site E-commerce',
        'Site clé en main',
        'Stockage et sauvegarde',
        'Système de paiement',
        'Système embarqué',
        'Sécurité',
        'Télémarketing',
        'Unity',
        'Video',
        'Web Analytics',
        'Web design',
        'WordPress',
        'XML',
        'liens',
        'sécurité',
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $name) {
            OfferCategory::firstOrCreate(['name' => $name], ['is_active' => true]);
        }
    }
}

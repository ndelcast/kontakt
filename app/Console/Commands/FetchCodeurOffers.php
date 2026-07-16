<?php

namespace App\Console\Commands;

use App\Services\CodeurFeedImporter;
use Illuminate\Console\Command;

class FetchCodeurOffers extends Command
{
    protected $signature = 'offers:fetch-codeur {--force : Ignorer l\'ETag mémorisé et retélécharger le flux}';

    protected $description = 'Récupère les offres publiées sur le flux RSS de Codeur';

    public function handle(CodeurFeedImporter $importer): int
    {
        $result = $importer->import(force: (bool) $this->option('force'));

        if (! $result->success) {
            $this->error($result->message());

            return self::FAILURE;
        }

        if ($result->unchanged) {
            $this->info($result->message());

            return self::SUCCESS;
        }

        $this->info(sprintf(
            'Reçues : %d — créées : %d — déjà connues : %d — nouvelles catégories : %d',
            $result->received,
            $result->created,
            $result->known,
            $result->newCategories
        ));

        return self::SUCCESS;
    }
}

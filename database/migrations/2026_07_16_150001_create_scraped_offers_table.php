<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Volontairement sans `team_id` ni trait BelongsToTenant : les offres Codeur
     * sont des données publiques globales, identiques pour tout le monde. Voir
     * docs/BRIEF-kontak-offres-codeur.md — c'est une dérogation assumée à la
     * convention multi-tenant du reste du projet.
     */
    public function up(): void
    {
        Schema::create('scraped_offers', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('url');
            $table->string('budget_raw')->nullable();
            $table->unsignedInteger('budget_min')->nullable();
            $table->unsignedInteger('budget_max')->nullable();
            $table->json('categories');
            $table->timestamp('published_at')->nullable();
            $table->string('status')->default('new');
            $table->foreignId('converted_opportunity_id')->nullable()->constrained('opportunities')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scraped_offers');
    }
};

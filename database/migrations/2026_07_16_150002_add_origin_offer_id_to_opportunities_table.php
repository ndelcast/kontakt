<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Traçabilité inverse : depuis une opportunité, retrouver l'offre dont elle
     * est issue. Le lien aller (scraped_offers.converted_opportunity_id) existe
     * déjà.
     */
    public function up(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->foreignId('origin_offer_id')
                ->nullable()
                ->after('contact_id')
                ->constrained('scraped_offers')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropForeign(['origin_offer_id']);
            $table->dropColumn('origin_offer_id');
        });
    }
};

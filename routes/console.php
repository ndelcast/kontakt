<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Le flux Codeur est une fenêtre glissante de 35 items seulement, et il tombe
// par rafales (8 offres en 2 minutes ont été observées). À ce rythme la fenêtre
// se vide en une dizaine de minutes : un passage horaire raterait des offres.
// Le flux honorant les requêtes conditionnelles, ces passages sont peu coûteux.
// Laravel ne propose pas de pas de 20 minutes (everyFifteenMinutes puis
// everyThirtyMinutes), d'où l'expression cron : déclenche à :00, :20 et :40.
Schedule::command('offers:fetch-codeur')
    ->cron('*/20 * * * *')
    ->withoutOverlapping();

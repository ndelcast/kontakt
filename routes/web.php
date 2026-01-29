<?php

use App\Http\Controllers\TeamInvitationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Team invitation acceptance
Route::get('/invitation/{token}', [TeamInvitationController::class, 'accept'])
    ->name('team-invitation.accept');

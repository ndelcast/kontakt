<?php

use App\Http\Controllers\App\CompanyController;
use App\Http\Controllers\App\ContactController;
use App\Http\Controllers\App\DashboardController;
use App\Http\Controllers\App\MarketController;
use App\Http\Controllers\App\OpportunityController;
use App\Http\Controllers\App\PipelineStageController;
use App\Http\Controllers\App\ProfileController;
use App\Http\Controllers\App\TaskController;
use App\Http\Controllers\App\TeamController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\TeamInvitationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(Auth::check() ? 'app.dashboard' : 'login');
});

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');
Route::get('/pending-approval', [LoginController::class, 'pendingApproval'])->name('pending-approval')->middleware('auth');

// Team invitation acceptance
Route::get('/invitation/{token}', [TeamInvitationController::class, 'accept'])
    ->name('team-invitation.accept');

// Inertia / PrimeVue application
Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsApproved::class])->prefix('app')->name('app.')->group(function () {
    // Dashboard
    Route::get('/', DashboardController::class)->name('dashboard');

    // Contacts
    Route::resource('contacts', ContactController::class)->except(['show']);

    // Companies
    Route::resource('companies', CompanyController::class);

    // Opportunities
    Route::get('opportunities/kanban', [OpportunityController::class, 'kanban'])->name('opportunities.kanban');
    Route::post('opportunities/kanban/move', [OpportunityController::class, 'kanbanMove'])->name('opportunities.kanban.move');
    Route::resource('opportunities', OpportunityController::class)->except(['show']);

    // Tasks
    Route::get('tasks/my-day', [TaskController::class, 'myDay'])->name('tasks.my-day');
    Route::post('tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
    Route::resource('tasks', TaskController::class)->except(['show']);

    // Marché (offres Codeur)
    Route::get('market', [MarketController::class, 'index'])->name('market.index');
    Route::put('market/offers/{offer}/status', [MarketController::class, 'updateStatus'])->name('market.offers.status');
    Route::get('market/categories', [MarketController::class, 'categories'])->name('market.categories');
    Route::put('market/categories', [MarketController::class, 'updateCategories'])->name('market.categories.update');

    // Pipeline Stages
    Route::post('pipeline/reorder', [PipelineStageController::class, 'reorder'])->name('pipeline.reorder');
    Route::resource('pipeline', PipelineStageController::class)->except(['show'])->parameters(['pipeline' => 'stage']);

    // Team
    Route::get('team/members', [TeamController::class, 'members'])->name('team.members');
    Route::post('team/invite', [TeamController::class, 'invite'])->name('team.invite');
    Route::delete('team/invitations/{invitation}', [TeamController::class, 'cancelInvitation'])->name('team.invitation.cancel');
    Route::put('team/{team}/members/{userId}/role', [TeamController::class, 'updateMemberRole'])->name('team.member.role');
    Route::delete('team/{team}/members/{userId}', [TeamController::class, 'removeMember'])->name('team.member.remove');
    Route::post('team/switch/{team}', [TeamController::class, 'switchTeam'])->name('team.switch');
    Route::get('team/create', [TeamController::class, 'create'])->name('team.create');
    Route::post('team', [TeamController::class, 'store'])->name('team.store');
    Route::get('team/edit', [TeamController::class, 'edit'])->name('team.edit');
    Route::put('team', [TeamController::class, 'update'])->name('team.update');

    // Profile
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
});

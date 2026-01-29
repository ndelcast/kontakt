<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Team;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CreateTeam extends RegisterTenant
{
    public static function getLabel(): string
    {
        return __('Create Team');
    }

    public function mount(): void
    {
        // Redirect to login if not authenticated
        if (! Auth::check()) {
            redirect()->to(Filament::getLoginUrl());
            return;
        }

        parent::mount();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label(__('Team Name'))
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label(__('Description'))
                    ->maxLength(1000)
                    ->rows(3),
            ]);
    }

    protected function handleRegistration(array $data): Model
    {
        // Create unique slug
        $baseSlug = Str::slug($data['name']);
        $slug = $baseSlug;
        $counter = 1;

        while (Team::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $team = Team::create([
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
        ]);

        // Attach current user to team as admin
        $user = auth()->user();
        $team->users()->attach($user, ['role' => 'admin']);

        // Set as current team if user has none
        if (!$user->current_team_id) {
            $user->update(['current_team_id' => $team->id]);
        }

        return $team;
    }
}

<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateSuperAdmin extends Command
{
    protected $signature = 'make:super-admin
                            {--email= : The email address}
                            {--name= : The name}
                            {--password= : The password}';

    protected $description = 'Create a Super Admin user with full platform access';

    public function handle(): int
    {
        $name = $this->option('name') ?? $this->ask('Name', 'Super Admin');
        $email = $this->option('email') ?? $this->ask('Email');
        $password = $this->option('password') ?? $this->secret('Password');

        if (! $email) {
            $this->error('Email is required.');
            return self::FAILURE;
        }

        if (User::where('email', $email)->exists()) {
            $this->error("User with email {$email} already exists.");
            return self::FAILURE;
        }

        // Create or get the admin team
        $team = Team::firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Administration']
        );

        // Create the Super Admin user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => UserRole::SuperAdmin,
            'approved_at' => now(),
            'current_team_id' => $team->id,
        ]);

        // Attach to admin team
        $user->teams()->attach($team, ['role' => 'admin']);

        $this->info('Super Admin created successfully!');
        $this->table(
            ['Field', 'Value'],
            [
                ['Name', $user->name],
                ['Email', $user->email],
                ['Role', 'Super Admin'],
                ['Team', $team->name],
            ]
        );

        $this->newLine();
        $this->info("Login at: " . url('/admin/login'));

        return self::SUCCESS;
    }
}

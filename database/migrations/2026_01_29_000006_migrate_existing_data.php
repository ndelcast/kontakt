<?php

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Create default team for existing data
        $defaultTeam = Team::create([
            'name' => 'Default Team',
            'slug' => 'default-team',
            'description' => 'Default team for migrated data',
        ]);

        // Get first user (if any) to be super admin
        $firstUser = User::first();

        if ($firstUser) {
            // Make first user super admin and approved
            $firstUser->update([
                'role' => 'super_admin',
                'approved_at' => now(),
                'current_team_id' => $defaultTeam->id,
            ]);

            // Attach first user to default team as admin
            DB::table('team_user')->insert([
                'team_id' => $defaultTeam->id,
                'user_id' => $firstUser->id,
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Approve and attach all other users to default team
            User::where('id', '!=', $firstUser->id)->get()->each(function ($user) use ($defaultTeam, $firstUser) {
                $user->update([
                    'approved_at' => now(),
                    'approved_by' => $firstUser->id,
                    'current_team_id' => $defaultTeam->id,
                ]);

                DB::table('team_user')->insert([
                    'team_id' => $defaultTeam->id,
                    'user_id' => $user->id,
                    'role' => 'member',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
        }

        // Migrate all existing data to default team
        DB::table('companies')->whereNull('team_id')->update(['team_id' => $defaultTeam->id]);
        DB::table('contacts')->whereNull('team_id')->update(['team_id' => $defaultTeam->id]);
        DB::table('opportunities')->whereNull('team_id')->update(['team_id' => $defaultTeam->id]);
        DB::table('tasks')->whereNull('team_id')->update(['team_id' => $defaultTeam->id]);
        DB::table('pipeline_stages')->whereNull('team_id')->update(['team_id' => $defaultTeam->id]);
    }

    public function down(): void
    {
        // Remove team_id from all records
        DB::table('companies')->update(['team_id' => null]);
        DB::table('contacts')->update(['team_id' => null]);
        DB::table('opportunities')->update(['team_id' => null]);
        DB::table('tasks')->update(['team_id' => null]);
        DB::table('pipeline_stages')->update(['team_id' => null]);

        // Remove default team
        Team::where('slug', 'default-team')->delete();
    }
};

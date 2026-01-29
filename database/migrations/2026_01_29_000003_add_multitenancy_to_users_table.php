<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('member')->after('email'); // 'super_admin', 'admin', 'member'
            $table->timestamp('approved_at')->nullable()->after('role');
            $table->foreignId('approved_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            $table->foreignId('current_team_id')->nullable()->after('approved_by')->constrained('teams')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['current_team_id']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['role', 'approved_at', 'approved_by', 'current_team_id']);
        });
    }
};

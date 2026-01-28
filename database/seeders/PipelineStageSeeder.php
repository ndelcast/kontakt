<?php

namespace Database\Seeders;

use App\Models\PipelineStage;
use Illuminate\Database\Seeder;

class PipelineStageSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            ['name' => 'Lead', 'slug' => 'lead', 'color' => '#6366f1', 'probability' => 10, 'position' => 1],
            ['name' => 'Qualified', 'slug' => 'qualified', 'color' => '#8b5cf6', 'probability' => 25, 'position' => 2],
            ['name' => 'Proposal', 'slug' => 'proposal', 'color' => '#f59e0b', 'probability' => 50, 'position' => 3],
            ['name' => 'Negotiation', 'slug' => 'negotiation', 'color' => '#f97316', 'probability' => 75, 'position' => 4],
            ['name' => 'Won', 'slug' => 'won', 'color' => '#22c55e', 'probability' => 100, 'position' => 5, 'is_won' => true],
            ['name' => 'Lost', 'slug' => 'lost', 'color' => '#ef4444', 'probability' => 0, 'position' => 6, 'is_lost' => true],
        ];

        foreach ($stages as $stage) {
            PipelineStage::create($stage);
        }
    }
}

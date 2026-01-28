<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\PipelineStage;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            ['name' => 'Acme Corp', 'industry' => 'Technology', 'website' => 'https://acme.example.com', 'phone' => '+1 555-0100'],
            ['name' => 'Globex Inc', 'industry' => 'Manufacturing', 'website' => 'https://globex.example.com', 'phone' => '+1 555-0200'],
            ['name' => 'Soylent Corp', 'industry' => 'Food & Beverage', 'website' => 'https://soylent.example.com', 'phone' => '+1 555-0300'],
            ['name' => 'Initech', 'industry' => 'Consulting', 'website' => 'https://initech.example.com', 'phone' => '+1 555-0400'],
            ['name' => 'Umbrella Corp', 'industry' => 'Pharmaceuticals', 'website' => 'https://umbrella.example.com', 'phone' => '+1 555-0500'],
        ];

        foreach ($companies as $data) {
            $company = Company::create($data);

            Contact::create([
                'company_id' => $company->id,
                'name' => fake()->name(),
                'email' => fake()->safeEmail(),
                'phone' => fake()->phoneNumber(),
                'position' => fake()->jobTitle(),
            ]);

            Contact::create([
                'company_id' => $company->id,
                'name' => fake()->name(),
                'email' => fake()->safeEmail(),
                'phone' => fake()->phoneNumber(),
                'position' => fake()->jobTitle(),
            ]);
        }

        $stages = PipelineStage::orderBy('position')->get();
        $contacts = Contact::all();

        $opportunityNames = [
            'Website Redesign', 'ERP Implementation', 'Cloud Migration',
            'Security Audit', 'Mobile App Development', 'Data Analytics Platform',
            'CRM Integration', 'API Development', 'DevOps Consulting',
            'AI Chatbot Project', 'E-commerce Platform', 'Marketing Automation',
        ];

        foreach ($opportunityNames as $i => $name) {
            $stage = $stages[$i % $stages->count()];
            $contact = $contacts->random();

            $opp = Opportunity::create([
                'pipeline_stage_id' => $stage->id,
                'company_id' => $contact->company_id,
                'contact_id' => $contact->id,
                'name' => $name,
                'value' => fake()->randomFloat(2, 5000, 150000),
                'expected_close_date' => now()->addDays(rand(7, 90)),
                'position' => $i + 1,
                'won_at' => $stage->is_won ? now()->subDays(rand(1, 30)) : null,
                'lost_at' => $stage->is_lost ? now()->subDays(rand(1, 30)) : null,
            ]);
        }
    }
}

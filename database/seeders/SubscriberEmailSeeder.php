<?php

namespace Database\Seeders;

use App\Models\SubscriberEmail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class SubscriberEmailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Log::info('Starting SubscriberEmail seeding');

        // Create 15 random subscribers with valid domains
        SubscriberEmail::factory()->count(15)->create();

        // Create 5 active and verified subscribers
        SubscriberEmail::factory()
            ->count(5)
            ->active()
            ->verified()
            ->create();

        // Create 3 active but unverified subscribers
        SubscriberEmail::factory()
            ->count(3)
            ->active()
            ->unverified()
            ->create();

        // Create 2 inactive subscribers
        SubscriberEmail::factory()
            ->count(2)
            ->inactive()
            ->create();

        // Create specific test emails for easy identification - with REAL domains
        // Be sure to replace these with your actual testing emails
        SubscriberEmail::create([
            'email' => 'test.subscriber@gmail.com', // Replace with your test email
            'name' => 'Test Subscriber',
            'is_active' => true,
            'verified_at' => now()->subDays(5),
        ]);

        SubscriberEmail::create([
            'email' => 'test.inactive@gmail.com', // Replace with your test email
            'name' => 'Inactive Subscriber',
            'is_active' => false,
            'verified_at' => now()->subDays(30),
        ]);

        SubscriberEmail::create([
            'email' => 'test.unverified@gmail.com', // Replace with your test email
            'name' => 'Unverified Subscriber',
            'is_active' => true,
            'verified_at' => null,
        ]);

        Log::info('Completed SubscriberEmail seeding', [
            'total_created' => SubscriberEmail::count()
        ]);
    }
}

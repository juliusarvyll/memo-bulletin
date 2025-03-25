<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SubscriberEmail;

class ResetSubscribers extends Command
{
    protected $signature = 'subscribers:reset';
    protected $description = 'Reset and reseed subscribers with valid email domains';

    public function handle()
    {
        $this->info('Resetting subscribers table...');

        // Delete all existing subscribers
        $count = SubscriberEmail::count();
        SubscriberEmail::truncate();
        $this->info("Deleted {$count} existing subscribers");

        // Run the seeder
        $this->info('Seeding new subscribers with valid domains...');
        $this->call('db:seed', ['--class' => 'SubscriberEmailSeeder']);

        $newCount = SubscriberEmail::count();
        $this->info("Created {$newCount} new subscribers with valid domains");

        return Command::SUCCESS;
    }
}

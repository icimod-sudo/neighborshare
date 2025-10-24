<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\ActivityTrackerService;
use Illuminate\Console\Command;

class TestActivityTracking extends Command
{
    protected $signature = 'test:activity {user_id}';
    protected $description = 'Test activity tracking for a user';

    public function handle()
    {
        $userId = $this->argument('user_id');

        try {
            $user = User::findOrFail($userId);

            $this->info("Testing activity tracking for user: {$user->name} ({$user->email})");

            // Test basic activity tracking
            $activity = ActivityTrackerService::track(
                'test_activity',
                'Test activity from command',
                ['test' => true],
                $user
            );

            $this->info("✅ Activity created successfully!");
            $this->info("Activity ID: {$activity->id}");
            $this->info("Type: {$activity->type}");
            $this->info("Description: {$activity->description}");

            // Check if activity exists in database
            $count = $user->activities()->count();
            $this->info("Total activities for user: {$count}");
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class DeleteUnverifiedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:cleanup-unverified';
    protected $description = 'Delete users who never verified phone/email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
      $count = User::whereNull('phone_verified_at')
            ->where('created_at', '<', now()->subMinutes(15))
            ->delete();

        $this->info("Deleted {$count} unverified users.");

        return Command::SUCCESS;
    }
}

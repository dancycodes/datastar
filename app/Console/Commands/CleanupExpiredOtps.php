<?php

namespace App\Console\Commands;

use App\Services\OtpService;
use Illuminate\Console\Command;

class CleanupExpiredOtps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'otp:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired OTP codes from the database';

    /**
     * Execute the console command.
     */
    public function handle(OtpService $otpService)
    {
        $this->info('Starting OTP cleanup process...');

        $deletedCount = $otpService->cleanupExpiredOtps();

        if ($deletedCount > 0) {
            $this->info("Successfully cleaned up {$deletedCount} expired OTP(s).");
        } else {
            $this->info('No expired OTPs found.');
        }

        return Command::SUCCESS;
    }
}
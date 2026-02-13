<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckSmsBalanceCommand extends Command
{
    protected $signature = 'check:sms-balance';

    protected $description = 'Quick check of SMS account balance';

    public function handle(): int
    {
        $this->info('📱 Checking SMS Balance...');
        $this->line('');

        try {
            $smsService = app(\App\Services\SmsService::class);
            $result = $smsService->checkBalance();

            if ($result['success']) {
                $balance = (float) ($result['balance'] ?? 0);
                $currency = $result['raw_response']['currency'] ?? 'MWK';

                $this->info("✓ Balance: {$balance} {$currency}");

                if ($balance < 20) {
                    $this->error('❌ Balance is too low to send SMS!');
                    $this->line('   You need at least 18-20 MWK per SMS');
                    $this->line('   Go to your dashboard and add credits');

                    return 1;
                }

                $this->info('✓ Sufficient balance to send SMS');

                return 0;
            }

            $this->error('❌ Failed to check balance');
            $this->line('Error: '.($result['error'] ?? 'Unknown error'));

            return 1;

        } catch (\Exception $e) {
            $this->error('❌ Error: '.$e->getMessage());

            return 1;
        }
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class DiagnosticOtpCommand extends Command
{
    protected $signature = 'diagnose:otp {--phone=+265888123456 : Phone number to test}';

    protected $description = 'Diagnose OTP sending issues with Telcomw API';

    public function handle(): int
    {
        $phone = $this->option('phone');

        $this->info('🔍 OTP System Diagnostic Report');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        // 1. Check configuration
        $this->checkConfiguration();

        // 2. Test WhatsApp API
        $this->testWhatsAppApi($phone);

        // 3. Check SMS Balance
        $this->checkSmsBalance();

        // 4. Test SMS API
        $this->testSmsApi($phone);

        // 5. Test OTP Service
        $this->testOtpService($phone);

        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('✓ Diagnostic complete. Check storage/logs/laravel.log for details.');

        return 0;
    }

    private function checkConfiguration(): void
    {
        $this->newLine();
        $this->info('1️⃣  Configuration Check');
        $this->line('─────────────────────');

        $config = config('services.telcomw');

        if (! $config) {
            $this->error('❌ Telcomw configuration not found!');

            return;
        }

        $this->line('Base URL:    '.($config['base_url'] ? '✓ '.$config['base_url'] : '❌ Not configured'));
        $this->line('API Key:     '.($config['api_key'] ? '✓ '.(strlen($config['api_key']) > 0 ? 'Set' : 'Empty') : '❌ Not configured'));
        $this->line('App ID:      '.($config['app_id'] ? '✓ '.$config['app_id'] : '❌ Not configured'));
        $this->line('Password:    '.($config['password'] ? '✓ '.(strlen($config['password']) > 0 ? 'Set' : 'Empty') : '❌ Not configured'));
        $this->line('Sender:      '.($config['sender'] ? '✓ '.$config['sender'] : '❌ Not configured'));
    }

    private function testWhatsAppApi(string $phone): void
    {
        $this->newLine();
        $this->info('2️⃣  WhatsApp API Test');
        $this->line('───────────────────');

        $config = config('services.telcomw');

        try {
            $response = Http::withHeaders([
                'x-api-key' => $config['api_key'],
                'x-app-id' => $config['app_id'],
                'Accept' => 'application/json',
            ])->post(
                "{$config['base_url']}/whatsapp/text",
                [
                    [
                        'recipient' => $phone,
                        'message' => 'Test message',
                    ],
                ]
            );

            $this->line('Status Code: '.$response->status());
            $this->line('Response:    '.$response->body());

            if ($response->status() === 404 && str_contains($response->body(), 'Invalid App ID')) {
                $this->warn('⚠️  App ID appears to be invalid for WhatsApp API');
                $this->line('   → Verify TELCOMW_APP_ID in your .env file');
            }
        } catch (\Exception $e) {
            $this->error('❌ WhatsApp API test failed: '.$e->getMessage());
        }
    }

    private function checkSmsBalance(): void
    {
        $this->newLine();
        $this->info('3️⃣  SMS Balance Check');
        $this->line('───────────────────');

        try {
            $smsService = app(\App\Services\SmsService::class);
            $result = $smsService->checkBalance();

            if ($result['success']) {
                $this->info('✓ Balance check successful');
                if (isset($result['balance'])) {
                    $balance = (float) $result['balance'];
                    $currency = $result['raw_response']['currency'] ?? 'MWK';

                    $this->line("Account Balance: {$result['balance']} {$currency}");

                    // Check if balance is sufficient (SMS typically costs 18-20 MWK)
                    if ($balance < 20) {
                        $this->error('⚠️  WARNING: Balance is critically low!');
                        $this->line('');
                        $this->warn('Your account balance is: '.$balance.' '.$currency);
                        $this->warn('Each SMS costs approximately 18-20 '.$currency);
                        $this->line('');
                        $this->info('You need to ADD CREDITS to your account');
                        $this->line('→ Go to your Telcomw dashboard');
                        $this->line('→ Add SMS credits/funds');
                        $this->line('→ Recommend adding at least 500 MWK');
                    } else {
                        $this->info('✓ Balance is sufficient for SMS sending');
                    }
                }
                if (isset($result['raw_response'])) {
                    $this->line('Full Response: '.json_encode($result['raw_response'], JSON_PRETTY_PRINT));
                }
            } else {
                $this->error('❌ Balance check failed');
                $this->line('Error: '.($result['error'] ?? 'Unknown error'));
                if (isset($result['raw_response'])) {
                    $this->line('Response: '.json_encode($result['raw_response'], JSON_PRETTY_PRINT));
                }
            }
        } catch (\Exception $e) {
            $this->error('❌ Balance check exception: '.$e->getMessage());
        }
    }

    private function testSmsApi(string $phone): void
    {
        $this->newLine();
        $this->info('4️⃣  SMS API Test');
        $this->line('───────────────');

        $config = config('services.telcomw');

        try {
            $response = Http::secure()->asForm()->post(
                "{$config['base_url']}/send",
                [
                    'api_key' => $config['api_key'],
                    'password' => $config['password'],
                    'text' => 'Your verification code is 123456. Valid for 5 minutes.',
                    'numbers' => $phone,
                    'from' => $config['sender'],
                ]
            );

            $this->line('Status Code: '.$response->status());
            $this->line('Response:    '.$response->body());

            $data = $response->json();

            // Check if feedback is false (account issue)
            if (($data['feedback'] ?? null) === false) {
                $this->error('❌ SMS delivery failed');
                $this->line('');
                $this->warn('The SMS API rejected your request.');
                $this->line('');
                $this->info('Possible causes (check above for balance):');
                $this->line('1. ⚠️  Account balance is TOO LOW (see balance check above)');
                $this->line('2. SMS service NOT ENABLED on account');
                $this->line('3. API credentials INVALID or INCOMPLETE');
                $this->line('4. API user lacks proper SMS permissions');

                return;
            }

            if ($data['type'] === 'success' && is_array($data['feedback'])) {
                $this->info('✓ SMS API test successful');
                $this->line('Feedback items: '.count($data['feedback']));
            }
        } catch (\Exception $e) {
            $this->error('❌ SMS API test failed: '.$e->getMessage());
        }
    }

    private function testOtpService(string $phone): void
    {
        $this->newLine();
        $this->info('5️⃣  OTP Service Test');
        $this->line('──────────────────');

        try {
            $smsService = app(\App\Services\SmsService::class);
            $testOtp = random_int(100000, 999999);

            $result = $smsService->sendOtp($phone, $testOtp);

            if ($result) {
                $this->info('✓ OTP service returned true');
                $this->warn('   Note: This only means WhatsApp or SMS API accepted the request.');
                $this->warn('   The message may still fail to deliver.');
            } else {
                $this->error('❌ OTP service returned false');
                $this->line('   Check storage/logs/laravel.log for error details');
            }
        } catch (\Exception $e) {
            $this->error('❌ OTP service test failed: '.$e->getMessage());
        }

        $this->line('');
        $this->info('Check recent logs:');
        $this->line('> php artisan tail');
    }
}

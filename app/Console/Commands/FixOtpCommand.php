<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FixOtpCommand extends Command
{
    protected $signature = 'fix:otp {--show-current : Show current .env values}';

    protected $description = 'Guide to fixing OTP/SMS issues - shows what needs to be fixed';

    public function handle(): int
    {
        $this->info('🚀 OTP/SMS Configuration Fix Guide');
        $this->line('═══════════════════════════════════════════════════════════');

        // Show problems
        $this->showProblems();

        // Show current configuration
        if ($this->option('show-current')) {
            $this->showCurrentConfiguration();
        }

        // Show what needs to be done
        $this->showFixSteps();

        return 0;
    }

    private function showProblems(): void
    {
        $this->newLine();
        $this->error('❌ Problems Detected:');
        $this->line('');
        $this->warn('1. WhatsApp App ID Invalid');
        $this->line('   Current value: TELCOMW_APP_ID=1');
        $this->line('   Problem: "1" is clearly a placeholder/placeholder value');
        $this->line('   Result: WhatsApp messages are rejected with 404 error');
        $this->line('');

        $this->warn('2. SMS Service Not Working');
        $this->line('   API returns: "feedback": false');
        $this->line('   Probable causes:');
        $this->line('   • Account has NO SMS CREDITS');
        $this->line('   • SMS service NOT ENABLED on account');
        $this->line('   • API credentials (TELCOMW_API_KEY, TELCOMW_PASSWORD) are INVALID');
        $this->line('   • API user account lacks permissions');
        $this->line('');
    }

    private function showCurrentConfiguration(): void
    {
        $this->newLine();
        $this->info('📋 Current Configuration (.env):');
        $this->line('─────────────────────────────────');

        $config = config('services.telcomw');

        foreach ($config as $key => $value) {
            if ($key === 'password') {
                $display = substr($value, 0, 3).str_repeat('*', max(0, strlen($value) - 6)).substr($value, -3);
            } else {
                $display = $value;
            }

            $this->line('TELCOMW_'.strtoupper($key)."=$display");
        }
    }

    private function showFixSteps(): void
    {
        $this->newLine();
        $this->info('✅ Steps to Fix:');
        $this->line('─────────────────────────────────');
        $this->line('');

        $this->line('1️⃣  Open Your Telcomw Account Dashboard');
        $this->line('   → Go to: https://dashboard.telcomw.com (or your account portal)');
        $this->line('   → Log in with your account credentials');
        $this->line('');

        $this->line('2️⃣  Verify & Get the Correct Values:');
        $this->line('');
        $this->warn('   a) API Key (TELCOMW_API_KEY)');
        $this->line('      • Find in: Settings > API > API Key');
        $this->line('      • This appears to be: zZC6D8HuuIxvczDT6kaM');
        $this->line('      • Verify it matches your account');
        $this->line('');

        $this->warn('   b) API Password (TELCOMW_PASSWORD)');
        $this->line('      • Find in: Settings > API > Password');
        $this->line('      • Current value contains: run%$Ace51186');
        $this->line('      • Note: The % might be causing URL encoding issues');
        $this->line('');

        $this->warn('   c) WhatsApp App ID (TELCOMW_APP_ID)');
        $this->line('      • Find in: Settings > WhatsApp Integration > App ID');
        $this->line('      • Current value "1" is INVALID - must be actual App ID');
        $this->line('      • If WhatsApp is not needed, you can disable it');
        $this->line('');

        $this->warn('   d) Sender ID (TELCOMW_SENDER)');
        $this->line('      • Current value: WGIT');
        $this->line('      • Verify this is registered with your account');
        $this->line('');

        $this->line('3️⃣  Check Account Status');
        $this->line('   ✓ Verify account has ACTIVE SMS CREDITS');
        $this->line('   ✓ Confirm SMS service is ENABLED');
        $this->line('   ✓ Check API user permissions for SMS and WhatsApp');
        $this->line('');

        $this->line('4️⃣  Update Your .env File');
        $this->line('   → Open: .env');
        $this->line('   → Update these lines with correct values from step 2:');
        $this->line('');

        $this->line('   TELCOMW_API_KEY=<your-actual-api-key>');
        $this->line('   TELCOMW_PASSWORD=<your-actual-password>');
        $this->line('   TELCOMW_APP_ID=<your-actual-app-id>');
        $this->line('   TELCOMW_SENDER=WGIT');
        $this->line('');

        $this->line('5️⃣  Test the Configuration');
        $this->line('   → Run: php artisan diagnose:otp --phone=+265XXXXXXXXX');
        $this->line('   → Should show ✓ success for SMS API test');
        $this->line('');

        $this->newLine();
        $this->info('💬 Need Help?');
        $this->line('─────────────────────────────────');
        $this->line('• Telcomw Support: https://telcomw.com/support');
        $this->line('• API Docs: https://documenter.getpostman.com/view/19359621/2s93XsY6B5');
        $this->line('• Check logs: php artisan tail');
        $this->line('');
    }
}

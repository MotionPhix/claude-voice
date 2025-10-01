<?php

namespace App\Http\Controllers;

use App\Models\InvoiceTemplate;
use App\Traits\HasCurrency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SettingsController extends Controller
{
    use HasCurrency;

    public function index()
    {
        $settings = $this->getSettings();
        $currencies = $this->getActiveCurrencies();
        $templates = InvoiceTemplate::all();

        return Inertia::render('settings/Index', [
            'settings' => $settings,
            'currencies' => $currencies,
            'templates' => $templates,
        ]);
    }

    public function company(Request $request)
    {
        if ($request->isMethod('post')) {
            return $this->updateCompanySettings($request);
        }

        $settings = $this->getSettings();

        return Inertia::render('settings/Company', [
            'settings' => $settings,
        ]);
    }

    public function invoice(Request $request)
    {
        if ($request->isMethod('post')) {
            return $this->updateInvoiceSettings($request);
        }

        $settings = $this->getSettings();
        $currencies = $this->getActiveCurrencies();
        $templates = InvoiceTemplate::all();

        return Inertia::render('settings/Invoice', [
            'settings' => $settings,
            'currencies' => $currencies,
            'templates' => $templates,
        ]);
    }

    public function email(Request $request)
    {
        if ($request->isMethod('post')) {
            return $this->updateEmailSettings($request);
        }

        $settings = $this->getSettings();

        return Inertia::render('settings/Email', [
            'settings' => $settings,
        ]);
    }

    public function payment(Request $request)
    {
        if ($request->isMethod('post')) {
            return $this->updatePaymentSettings($request);
        }

        $settings = $this->getSettings();

        return Inertia::render('settings/Payment', [
            'settings' => $settings,
        ]);
    }

    private function updateCompanySettings(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_email' => 'required|email|max:255',
            'company_phone' => 'nullable|string|max:50',
            'company_website' => 'nullable|url|max:255',
            'company_address' => 'nullable|string|max:500',
            'company_city' => 'nullable|string|max:100',
            'company_state' => 'nullable|string|max:100',
            'company_postal_code' => 'nullable|string|max:20',
            'company_country' => 'nullable|string|max:100',
            'company_tax_number' => 'nullable|string|max:50',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Handle logo upload
        if ($request->hasFile('company_logo')) {
            $logoPath = $request->file('company_logo')->store('company', 'public');
            $validated['company_logo'] = $logoPath;

            // Delete old logo if exists
            $oldLogo = $this->getSetting('company_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
        }

        foreach ($validated as $key => $value) {
            $this->setSetting($key, $value);
        }

        return redirect()->route('settings.company')
            ->with('success', 'Company settings updated successfully.');
    }

    private function updateInvoiceSettings(Request $request)
    {
        $validated = $request->validate([
            'default_currency' => 'required|exists:currencies,code',
            'invoice_prefix' => 'required|string|max:20',
            'invoice_number_format' => 'required|string|max:50',
            'default_payment_terms' => 'required|integer|min:0|max:365',
            'default_tax_rate' => 'required|numeric|min:0|max:100',
            'late_fee_rate' => 'nullable|numeric|min:0|max:100',
            'late_fee_days' => 'nullable|integer|min:1|max:365',
            'auto_send_reminders' => 'boolean',
            'reminder_days_before' => 'nullable|integer|min:1|max:30',
            'overdue_reminder_frequency' => 'nullable|in:daily,weekly,monthly',
            'default_invoice_template' => 'nullable|exists:invoice_templates,id',
        ]);

        foreach ($validated as $key => $value) {
            $this->setSetting($key, $value);
        }

        return redirect()->route('settings.invoice')
            ->with('success', 'Invoice settings updated successfully.');
    }

    private function updateEmailSettings(Request $request)
    {
        $validated = $request->validate([
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_encryption' => 'nullable|in:tls,ssl',
            'mail_from_address' => 'required|email|max:255',
            'mail_from_name' => 'required|string|max:255',
            'invoice_email_subject' => 'required|string|max:255',
            'invoice_email_body' => 'required|string',
            'reminder_email_subject' => 'required|string|max:255',
            'reminder_email_body' => 'required|string',
            'overdue_email_subject' => 'required|string|max:255',
            'overdue_email_body' => 'required|string',
        ]);

        foreach ($validated as $key => $value) {
            $this->setSetting($key, $value);
        }

        return redirect()->route('settings.email')
            ->with('success', 'Email settings updated successfully.');
    }

    private function updatePaymentSettings(Request $request)
    {
        $validated = $request->validate([
            'enable_online_payments' => 'boolean',
            'payment_gateway' => 'nullable|in:stripe,paypal,square',
            'stripe_public_key' => 'nullable|string|max:255',
            'stripe_secret_key' => 'nullable|string|max:255',
            'paypal_client_id' => 'nullable|string|max:255',
            'paypal_client_secret' => 'nullable|string|max:255',
            'paypal_mode' => 'nullable|in:sandbox,live',
            'payment_terms_text' => 'nullable|string',
            'bank_account_details' => 'nullable|string',
        ]);

        foreach ($validated as $key => $value) {
            $this->setSetting($key, $value);
        }

        return redirect()->route('settings.payment')
            ->with('success', 'Payment settings updated successfully.');
    }

    public function testEmail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        try {
            Mail::raw('This is a test email from your invoice system.', function ($message) use ($request) {
                $message->to($request->test_email)
                    ->subject('Test Email - Invoice System');
            });

            return response()->json(['success' => true, 'message' => 'Test email sent successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to send test email: '.$e->getMessage()]);
        }
    }

    public function backup()
    {
        try {
            // Create database backup
            $filename = 'backup_'.now()->format('Y_m_d_H_i_s').'.sql';
            $path = storage_path('app/backups/'.$filename);

            // Ensure backup directory exists
            if (! file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            // Run mysqldump command
            $command = sprintf(
                'mysqldump -h%s -u%s -p%s %s > %s',
                config('database.connections.mysql.host'),
                config('database.connections.mysql.username'),
                config('database.connections.mysql.password'),
                config('database.connections.mysql.database'),
                $path
            );

            exec($command, $output, $returnCode);

            if ($returnCode === 0) {
                return response()->download($path)->deleteFileAfterSend(false);
            } else {
                throw new \Exception('Backup command failed');
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create backup: '.$e->getMessage());
        }
    }

    public function clearCache()
    {
        try {
            Cache::flush();

            // Clear other caches
            \Artisan::call('config:clear');
            \Artisan::call('route:clear');
            \Artisan::call('view:clear');

            return redirect()->back()
                ->with('success', 'Cache cleared successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to clear cache: '.$e->getMessage());
        }
    }

    private function getSettings()
    {
        return Cache::remember('app_settings', 3600, function () {
            $defaultSettings = [
                // Company settings
                'company_name' => config('app.name'),
                'company_email' => '',
                'company_phone' => '',
                'company_website' => '',
                'company_address' => '',
                'company_city' => '',
                'company_state' => '',
                'company_postal_code' => '',
                'company_country' => '',
                'company_tax_number' => '',
                'company_logo' => '',

                // Invoice settings
                'default_currency' => 'USD',
                'invoice_prefix' => 'INV',
                'invoice_number_format' => 'PREFIX-YYYY-NNNN',
                'default_payment_terms' => 30,
                'default_tax_rate' => 0,
                'late_fee_rate' => 0,
                'late_fee_days' => 30,
                'auto_send_reminders' => false,
                'reminder_days_before' => 3,
                'overdue_reminder_frequency' => 'weekly',
                'default_invoice_template' => null,

                // Email settings
                'mail_from_address' => config('mail.from.address'),
                'mail_from_name' => config('mail.from.name'),
                'invoice_email_subject' => 'Invoice {invoice_number} from {company_name}',
                'invoice_email_body' => 'Dear {client_name},\n\nPlease find attached invoice {invoice_number} for {invoice_total}.\n\nThank you for your business!\n\n{company_name}',
                'reminder_email_subject' => 'Payment Reminder - Invoice {invoice_number}',
                'reminder_email_body' => 'Dear {client_name},\n\nThis is a friendly reminder that invoice {invoice_number} for {invoice_total} is due on {due_date}.\n\nThank you!\n\n{company_name}',
                'overdue_email_subject' => 'Overdue Payment - Invoice {invoice_number}',
                'overdue_email_body' => 'Dear {client_name},\n\nInvoice {invoice_number} for {invoice_total} is now overdue. Please arrange payment as soon as possible.\n\nThank you!\n\n{company_name}',

                // Payment settings
                'enable_online_payments' => false,
                'payment_gateway' => 'stripe',
                'payment_terms_text' => 'Payment is due within 30 days of invoice date.',
                'bank_account_details' => '',
            ];

            // Get settings from database/cache
            // This would typically come from a settings table
            return $defaultSettings;
        });
    }

    private function getSetting($key, $default = null)
    {
        $settings = $this->getSettings();

        return $settings[$key] ?? $default;
    }

    private function setSetting($key, $value)
    {
        // This would typically save to a settings table
        // For now, we'll just clear the cache so it gets refreshed
        Cache::forget('app_settings');

        // In a real implementation, you'd save to database here
        // Setting::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}

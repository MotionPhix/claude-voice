<?php

namespace App\Console\Commands;

use App\Models\RecurringInvoice;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\SystemNotificationService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class GenerateRecurringInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:generate-recurring
                            {--dry-run : Show what would be generated without creating invoices}
                            {--force : Generate even if not due yet}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate invoices from recurring invoice templates that are due';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('🔄 Checking for recurring invoices to generate...');

        $query = RecurringInvoice::with(['client', 'items'])
            ->where('is_active', true);

        if (!$force) {
            $query->where('next_invoice_date', '<=', now());
        }

        // Check if max cycles reached
        $query->where(function ($q) {
            $q->whereNull('max_cycles')
                ->orWhereColumn('cycles_completed', '<', 'max_cycles');
        });

        // Check if end date hasn't passed
        $query->where(function ($q) {
            $q->whereNull('end_date')
                ->orWhere('end_date', '>=', now());
        });

        $recurringInvoices = $query->get();

        if ($recurringInvoices->isEmpty()) {
            $this->info('✅ No recurring invoices need to be generated.');
            return Command::SUCCESS;
        }

        $this->info("Found {$recurringInvoices->count()} recurring invoice(s) ready for generation:");

        $headers = ['ID', 'Name', 'Client', 'Next Due', 'Frequency', 'Amount', 'Cycles'];
        $rows = [];

        foreach ($recurringInvoices as $recurring) {
            $cyclesInfo = $recurring->max_cycles 
                ? "{$recurring->cycles_completed}/{$recurring->max_cycles}"
                : "{$recurring->cycles_completed}/∞";

            $rows[] = [
                $recurring->id,
                $recurring->name,
                $recurring->client->name,
                $recurring->next_invoice_date->format('Y-m-d'),
                ucfirst($recurring->frequency),
                '$' . number_format($recurring->total, 2),
                $cyclesInfo,
            ];
        }

        $this->table($headers, $rows);

        if ($dryRun) {
            $this->warn('🚨 DRY RUN: No invoices were generated.');
            return Command::SUCCESS;
        }

        if (!$this->confirm('Do you want to generate these invoices?')) {
            $this->info('❌ Operation cancelled.');
            return Command::SUCCESS;
        }

        $generated = 0;
        $notificationService = app(SystemNotificationService::class);

        foreach ($recurringInvoices as $recurring) {
            try {
                $invoice = $this->generateInvoiceFromRecurring($recurring);
                $generated++;

                $this->line("✅ Generated invoice #{$invoice->invoice_number} from '{$recurring->name}'");

                // Update recurring invoice
                $recurring->increment('cycles_completed');
                $recurring->update([
                    'next_invoice_date' => $this->calculateNextInvoiceDate($recurring)
                ]);

                // Send notification
                $notificationService->notifyRecurringInvoiceGenerated($invoice, $recurring);

                // Deactivate if max cycles reached
                if ($recurring->max_cycles && $recurring->cycles_completed >= $recurring->max_cycles) {
                    $recurring->update(['is_active' => false]);
                    $this->line("🛑 Deactivated '{$recurring->name}' - max cycles reached");
                }

                // Deactivate if end date passed
                if ($recurring->end_date && $recurring->end_date->isPast()) {
                    $recurring->update(['is_active' => false]);
                    $this->line("🛑 Deactivated '{$recurring->name}' - end date reached");
                }

            } catch (\Exception $e) {
                $this->error("❌ Failed to generate invoice from '{$recurring->name}': {$e->getMessage()}");
            }
        }

        $this->info("🎉 Successfully generated {$generated} invoice(s) from recurring templates.");

        return Command::SUCCESS;
    }

    /**
     * Generate an invoice from a recurring invoice template.
     */
    private function generateInvoiceFromRecurring(RecurringInvoice $recurring): Invoice
    {
        $dueDate = now()->addDays($recurring->payment_terms_days ?? 30);

        $invoice = Invoice::create([
            'client_id' => $recurring->client_id,
            'recurring_invoice_id' => $recurring->id,
            'currency' => $recurring->currency,
            'issue_date' => now()->toDateString(),
            'due_date' => $dueDate->toDateString(),
            'status' => 'draft',
            'tax_rate' => $recurring->tax_rate,
            'discount' => $recurring->discount,
            'notes' => $recurring->notes,
            'terms' => $recurring->terms,
        ]);

        // Copy items from recurring invoice
        foreach ($recurring->items as $recurringItem) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $recurringItem->description,
                'quantity' => $recurringItem->quantity,
                'unit_price' => $recurringItem->unit_price,
                'total' => $recurringItem->total,
            ]);
        }

        $invoice->calculateTotals();

        return $invoice;
    }

    /**
     * Calculate the next invoice date based on frequency and interval.
     */
    private function calculateNextInvoiceDate(RecurringInvoice $recurring): Carbon
    {
        $current = $recurring->next_invoice_date;
        $interval = $recurring->interval ?? 1;

        return match ($recurring->frequency) {
            'daily' => $current->addDays($interval),
            'weekly' => $current->addWeeks($interval),
            'monthly' => $current->addMonths($interval),
            'quarterly' => $current->addMonths($interval * 3),
            'yearly' => $current->addYears($interval),
            default => $current->addMonths(1),
        };
    }
}

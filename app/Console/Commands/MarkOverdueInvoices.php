<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\SystemNotificationService;
use Illuminate\Console\Command;

class MarkOverdueInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:mark-overdue 
                            {--dry-run : Show what would be updated without making changes}
                            {--notify : Send notifications for newly overdue invoices}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark sent invoices as overdue if they have passed their due date';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $notify = $this->option('notify');

        $this->info('🔍 Checking for overdue invoices...');

        $overdueInvoices = Invoice::where('status', 'sent')
            ->where('due_date', '<', now())
            ->get();

        if ($overdueInvoices->isEmpty()) {
            $this->info('✅ No invoices to mark as overdue.');
            return Command::SUCCESS;
        }

        $this->info("Found {$overdueInvoices->count()} invoice(s) to mark as overdue:");

        $headers = ['ID', 'Invoice Number', 'Client', 'Due Date', 'Amount', 'Days Overdue'];
        $rows = [];

        foreach ($overdueInvoices as $invoice) {
            $daysOverdue = now()->diffInDays($invoice->due_date);
            $rows[] = [
                $invoice->id,
                $invoice->invoice_number,
                $invoice->client->name,
                $invoice->due_date->format('Y-m-d'),
                '$' . number_format($invoice->total, 2),
                $daysOverdue . ' days',
            ];
        }

        $this->table($headers, $rows);

        if ($dryRun) {
            $this->warn('🚨 DRY RUN: No changes were made to the database.');
            return Command::SUCCESS;
        }

        if (!$this->confirm('Do you want to mark these invoices as overdue?')) {
            $this->info('❌ Operation cancelled.');
            return Command::SUCCESS;
        }

        $updated = 0;
        $notificationService = app(SystemNotificationService::class);

        foreach ($overdueInvoices as $invoice) {
            $invoice->update(['status' => 'overdue']);
            $updated++;

            if ($notify) {
                $notificationService->notifyInvoiceOverdue($invoice);
            }

            $this->line("✅ Marked invoice #{$invoice->invoice_number} as overdue");
        }

        $this->info("🎉 Successfully marked {$updated} invoice(s) as overdue.");

        if ($notify) {
            $this->info("📧 Sent {$updated} overdue notifications.");
        }

        return Command::SUCCESS;
    }
}

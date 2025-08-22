<?php

namespace App\Console\Commands;

use App\Models\RecurringInvoice;
use Illuminate\Console\Command;

class GenerateRecurringInvoices extends Command
{
    protected $signature = 'invoices:generate-recurring';
    protected $description = 'Generate invoices from active recurring invoices that are due';

    public function handle()
    {
        $recurringInvoices = RecurringInvoice::active()
            ->dueToday()
            ->with(['client', 'items'])
            ->get();

        $generatedCount = 0;
        $errors = [];

        foreach ($recurringInvoices as $recurringInvoice) {
            try {
                $invoice = $recurringInvoice->generateInvoice();

                if ($invoice) {
                    $generatedCount++;
                    $this->info("Generated invoice #{$invoice->invoice_number} for {$recurringInvoice->client->name}");
                } else {
                    $errors[] = "Failed to generate invoice for recurring invoice #{$recurringInvoice->id}";
                }
            } catch (\Exception $e) {
                $errors[] = "Error generating invoice for recurring invoice #{$recurringInvoice->id}: " . $e->getMessage();
                $this->error($errors[count($errors) - 1]);
            }
        }

        $this->info("Generated {$generatedCount} invoices from recurring invoices.");

        if (!empty($errors)) {
            $this->warn("Encountered " . count($errors) . " errors:");
            foreach ($errors as $error) {
                $this->error($error);
            }
        }

        return $generatedCount > 0 ? 0 : 1;
    }
}

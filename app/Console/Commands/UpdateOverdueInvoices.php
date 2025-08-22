<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;

class UpdateOverdueInvoices extends Command
{
    protected $signature = 'invoices:update-overdue';
    protected $description = 'Update invoice status to overdue for past due invoices';

    public function handle()
    {
        $count = Invoice::where('status', 'sent')
            ->where('due_date', '<', now())
            ->update(['status' => 'overdue']);

        $this->info("Updated {$count} invoices to overdue status.");
    }
}

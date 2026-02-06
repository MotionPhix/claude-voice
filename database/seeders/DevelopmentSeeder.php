<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevelopmentSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Create or find admin user with their own organization as owner
    $admin = User::firstOrCreate(
      ['email' => 'demo@claude-voice.com'],
      [
        'name' => 'Maxwell Estes',
        'password' => Hash::make('password'),
        'email_verified_at' => now(),
      ]
    );

    // Ensure admin has an organization and membership
    if (!$admin->activeOrganizations->count()) {
      $organization = Organization::factory()->create([
        'name' => 'Laravel Demo Organization',
        'slug' => 'laravel-demo'
      ]);

      Membership::factory()
        ->owner()
        ->for($admin)
        ->for($organization)
        ->create();
    } else {
      $organization = $admin->activeOrganizations->first();
    }

    // Set the current organization in session for proper scoping
    set_current_organization($organization);

    // Create sample clients for this organization
    $clients = Client::factory(15)->create(['organization_id' => $organization->id]);

    // Create sample invoices with different statuses
    $invoices = collect();

    // Draft invoices
    $draftInvoices = Invoice::factory(5)
      ->draft()
      ->sequence(fn($sequence) => [
        'client_id' => $clients->random()->id,
        'organization_id' => $organization->id
      ])
      ->create();
    $invoices = $invoices->merge($draftInvoices);

    // Sent invoices
    $sentInvoices = Invoice::factory(8)
      ->sent()
      ->sequence(fn($sequence) => [
        'client_id' => $clients->random()->id,
        'organization_id' => $organization->id
      ])
      ->create();
    $invoices = $invoices->merge($sentInvoices);

    // Paid invoices
    $paidInvoices = Invoice::factory(12)
      ->paid()
      ->sequence(fn($sequence) => [
        'client_id' => $clients->random()->id,
        'organization_id' => $organization->id
      ])
      ->create();
    $invoices = $invoices->merge($paidInvoices);

    // Overdue invoices
    $overdueInvoices = Invoice::factory(4)
      ->overdue()
      ->sequence(fn($sequence) => [
        'client_id' => $clients->random()->id,
        'organization_id' => $organization->id
      ])
      ->create();
    $invoices = $invoices->merge($overdueInvoices);

    // Add invoice items to all invoices
    $invoices->each(function ($invoice) {
      $itemCount = rand(1, 5);
      $items = InvoiceItem::factory($itemCount)->for($invoice)->create();

      $invoice->calculateTotals();
    });

    // Add payments to paid invoices
    $paidInvoices->each(function ($invoice) {
      // Full payment for paid invoices (manual payments)
      Payment::factory()->for($invoice)->create([
        'amount' => $invoice->total,
        'payment_date' => $invoice->paid_at ?? now(),
        'gateway' => 'manual',
        'status' => 'completed',
        'completed_at' => $invoice->paid_at ?? now(),
      ]);
    });

    // Add partial payments to some sent invoices (mix of manual and PayChangu simulated)
    $sentInvoices->random(3)->each(function ($invoice) {
      $partialAmount = $invoice->total * rand(30, 70) / 100;
      $isPayChangu = rand(0, 1); // Randomly simulate PayChangu or manual

      Payment::factory()->for($invoice)->create([
        'amount' => round($partialAmount, 2),
        'payment_date' => now()->subDays(rand(1, 10)),
        'gateway' => $isPayChangu ? 'paychangu' : 'manual',
        'status' => 'completed',
        'channel' => $isPayChangu ? ['Card', 'Mobile Money', 'Bank Transfer'][rand(0, 2)] : null,
        'tx_ref' => $isPayChangu ? 'INV-' . $invoice->id . '-' . strtoupper(bin2hex(random_bytes(4))) : null,
        'completed_at' => now()->subDays(rand(1, 10)),
      ]);
    });

    // Create some invoices with multiple currencies (African currencies for regional testing)
    $africanCurrencies = ['ZAR', 'KES', 'NGN'];
    foreach ($africanCurrencies as $currency) {
      Invoice::factory(2)
        ->withCurrency($currency)
        ->sequence(fn($sequence) => [
          'client_id' => $clients->random()->id,
          'organization_id' => $organization->id
        ])
        ->create()
        ->each(function ($invoice) {
          InvoiceItem::factory(rand(1, 3))->for($invoice)->create();
          $invoice->calculateTotals();
        });
    }

    $this->command->info('Development data created:');
    $this->command->info('- 1 owner user (' . $admin->email . ' / password)');
    $this->command->info('- 1 organization (' . $organization->name . ')');
    $this->command->info('- ' . $clients->count() . ' clients');
    $this->command->info('- ' . Invoice::count() . ' invoices');
    $this->command->info('- ' . InvoiceItem::count() . ' invoice items');
    $this->command->info('- ' . Payment::count() . ' payments');
  }
}

<?php

namespace Database\Seeders;

use App\Models\Membership;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProductionSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * This seeder is designed to be run in production to set up
   * the initial admin user and organization.
   */
  public function run(): void
  {
    // Check if there are already users in the system
    if (User::exists()) {
      $this->command->warn('Users already exist. Skipping production seeding.');
      return;
    }

    // Create the first admin user
    $adminEmail = $this->command->ask('Enter admin email', 'admin@example.com');
    $adminName = $this->command->ask('Enter admin name', 'Admin User');
    $adminPassword = $this->command->secret('Enter admin password');

    if (!$adminPassword) {
      $this->command->error('Password is required.');
      return;
    }

    $admin = User::create([
      'name' => $adminName,
      'email' => $adminEmail,
      'password' => Hash::make($adminPassword),
      'email_verified_at' => now(),
    ]);

    // Create the first organization
    $orgName = $this->command->ask('Enter organization name', config('app.name', 'Invoice System'));
    $orgEmail = $this->command->ask('Enter organization email', $adminEmail);

    $organization = Organization::create([
      'name' => $orgName,
      'slug' => Str::slug($orgName),
      'email' => $orgEmail,
      'is_active' => true,
      'settings' => [
        'currency' => 'USD',
        'timezone' => config('app.timezone', 'UTC'),
        'date_format' => 'Y-m-d',
        'invoice_prefix' => 'INV',
      ],
    ]);

    // Create owner membership
    Membership::factory()->owner()->create([
      'user_id' => $admin->id,
      'organization_id' => $organization->id,
    ]);

    $this->command->info('Production setup completed!');
    $this->command->info('Admin User: ' . $admin->email);
    $this->command->info('Organization: ' . $organization->name);
    $this->command->warn('Please save your credentials securely!');
  }
}

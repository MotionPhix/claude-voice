<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   *
   * Usage:
   * - Local: `php artisan db:seed` or `php artisan migrate:fresh --seed`
   * - Production: `php artisan db:seed --class=CurrencySeeder` then `php artisan db:seed --class=ProductionSeeder`
   */
  public function run(): void
  {
    // Always seed essential data needed for both local and production
    $this->call([
      CurrencySeeder::class,
    ]);

    // Seed development data only in local environment
    if (app()->environment('local')) {
      $this->call([
        DevelopmentSeeder::class,
      ]);
    }
  }
}

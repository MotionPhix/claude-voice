<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $currencies = [
      // Major global currencies
      ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'is_base' => true, 'is_active' => true, 'exchange_rate' => 1.000000],
      ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'is_active' => true, 'exchange_rate' => 0.850000],
      ['code' => 'GBP', 'name' => 'British Pound Sterling', 'symbol' => '£', 'is_active' => true, 'exchange_rate' => 0.730000],
      ['code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => '¥', 'is_active' => true, 'exchange_rate' => 149.000000],
      ['code' => 'CHF', 'name' => 'Swiss Franc', 'symbol' => 'CHF', 'is_active' => true, 'exchange_rate' => 0.910000],
      ['code' => 'CAD', 'name' => 'Canadian Dollar', 'symbol' => 'C$', 'is_active' => true, 'exchange_rate' => 1.350000],
      ['code' => 'AUD', 'name' => 'Australian Dollar', 'symbol' => 'A$', 'is_active' => true, 'exchange_rate' => 1.520000],
      ['code' => 'CNY', 'name' => 'Chinese Yuan', 'symbol' => '¥', 'is_active' => true, 'exchange_rate' => 7.200000],

      // African currencies
      ['code' => 'ZAR', 'name' => 'South African Rand', 'symbol' => 'R', 'is_active' => true, 'exchange_rate' => 18.500000],
      ['code' => 'ZMW', 'name' => 'Zambian Kwacha', 'symbol' => 'ZK', 'is_active' => true, 'exchange_rate' => 27.000000],
      ['code' => 'ZWL', 'name' => 'Zimbabwean Dollar', 'symbol' => 'Z$', 'is_active' => true, 'exchange_rate' => 322.000000],
      ['code' => 'MWK', 'name' => 'Malawian Kwacha', 'symbol' => 'MK', 'is_active' => true, 'exchange_rate' => 1730.000000],
      ['code' => 'TZS', 'name' => 'Tanzanian Shilling', 'symbol' => 'TSh', 'is_active' => true, 'exchange_rate' => 2500.000000],
      ['code' => 'KES', 'name' => 'Kenyan Shilling', 'symbol' => 'KSh', 'is_active' => true, 'exchange_rate' => 149.000000],
      ['code' => 'NGN', 'name' => 'Nigerian Naira', 'symbol' => '₦', 'is_active' => true, 'exchange_rate' => 1500.000000],
      ['code' => 'EGP', 'name' => 'Egyptian Pound', 'symbol' => 'E£', 'is_active' => true, 'exchange_rate' => 49.000000],
      ['code' => 'GHS', 'name' => 'Ghanaian Cedi', 'symbol' => 'GH₵', 'is_active' => true, 'exchange_rate' => 15.500000],

      // Other major currencies
      ['code' => 'INR', 'name' => 'Indian Rupee', 'symbol' => '₹', 'is_active' => true, 'exchange_rate' => 83.000000],
      ['code' => 'AED', 'name' => 'UAE Dirham', 'symbol' => 'د.إ', 'is_active' => true, 'exchange_rate' => 3.670000],
      ['code' => 'BRL', 'name' => 'Brazilian Real', 'symbol' => 'R$', 'is_active' => true, 'exchange_rate' => 5.000000],
    ];

    foreach ($currencies as $currency) {
      Currency::updateOrCreate(
        ['code' => $currency['code']],
        $currency
      );
    }
  }
}

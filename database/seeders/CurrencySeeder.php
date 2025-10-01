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
            // Major currencies
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'is_base' => true, 'is_active' => true, 'exchange_rate' => 1.000000],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'is_active' => true, 'exchange_rate' => 0.850000],
            ['code' => 'GBP', 'name' => 'British Pound Sterling', 'symbol' => '£', 'is_active' => true, 'exchange_rate' => 0.730000],
            ['code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => '¥', 'is_active' => true, 'exchange_rate' => 149.000000],
            ['code' => 'CHF', 'name' => 'Swiss Franc', 'symbol' => 'CHF', 'is_active' => true, 'exchange_rate' => 0.910000],
            ['code' => 'CAD', 'name' => 'Canadian Dollar', 'symbol' => 'C$', 'is_active' => true, 'exchange_rate' => 1.350000],
            ['code' => 'AUD', 'name' => 'Australian Dollar', 'symbol' => 'A$', 'is_active' => true, 'exchange_rate' => 1.520000],
            ['code' => 'CNY', 'name' => 'Chinese Yuan Renminbi', 'symbol' => '¥', 'is_active' => true, 'exchange_rate' => 7.200000],
            ['code' => 'NZD', 'name' => 'New Zealand Dollar', 'symbol' => 'NZ$', 'is_active' => true, 'exchange_rate' => 1.640000],
            ['code' => 'SEK', 'name' => 'Swedish Krona', 'symbol' => 'kr', 'is_active' => true, 'exchange_rate' => 10.500000],
            ['code' => 'NOK', 'name' => 'Norwegian Krone', 'symbol' => 'kr', 'is_active' => true, 'exchange_rate' => 10.800000],
            ['code' => 'DKK', 'name' => 'Danish Krone', 'symbol' => 'kr', 'is_active' => true, 'exchange_rate' => 6.850000],

            // Asian currencies
            ['code' => 'INR', 'name' => 'Indian Rupee', 'symbol' => '₹', 'is_active' => true, 'exchange_rate' => 83.000000],
            ['code' => 'KRW', 'name' => 'South Korean Won', 'symbol' => '₩', 'is_active' => true, 'exchange_rate' => 1320.000000],
            ['code' => 'SGD', 'name' => 'Singapore Dollar', 'symbol' => 'S$', 'is_active' => true, 'exchange_rate' => 1.350000],
            ['code' => 'HKD', 'name' => 'Hong Kong Dollar', 'symbol' => 'HK$', 'is_active' => true, 'exchange_rate' => 7.800000],
            ['code' => 'TWD', 'name' => 'Taiwan New Dollar', 'symbol' => 'NT$', 'is_active' => true, 'exchange_rate' => 31.500000],
            ['code' => 'THB', 'name' => 'Thai Baht', 'symbol' => '฿', 'is_active' => true, 'exchange_rate' => 35.500000],
            ['code' => 'MYR', 'name' => 'Malaysian Ringgit', 'symbol' => 'RM', 'is_active' => true, 'exchange_rate' => 4.650000],
            ['code' => 'IDR', 'name' => 'Indonesian Rupiah', 'symbol' => 'Rp', 'is_active' => true, 'exchange_rate' => 15200.000000],
            ['code' => 'PHP', 'name' => 'Philippine Peso', 'symbol' => '₱', 'is_active' => true, 'exchange_rate' => 55.500000],
            ['code' => 'VND', 'name' => 'Vietnamese Dong', 'symbol' => '₫', 'is_active' => true, 'exchange_rate' => 24000.000000],

            // Middle Eastern currencies
            ['code' => 'AED', 'name' => 'UAE Dirham', 'symbol' => 'د.إ', 'is_active' => true, 'exchange_rate' => 3.670000],
            ['code' => 'SAR', 'name' => 'Saudi Riyal', 'symbol' => '﷼', 'is_active' => true, 'exchange_rate' => 3.750000],
            ['code' => 'QAR', 'name' => 'Qatari Rial', 'symbol' => '﷼', 'is_active' => true, 'exchange_rate' => 3.640000],
            ['code' => 'KWD', 'name' => 'Kuwaiti Dinar', 'symbol' => 'د.ك', 'is_active' => true, 'exchange_rate' => 0.308000],
            ['code' => 'BHD', 'name' => 'Bahraini Dinar', 'symbol' => '.د.ب', 'is_active' => true, 'exchange_rate' => 0.377000],
            ['code' => 'OMR', 'name' => 'Omani Rial', 'symbol' => '﷼', 'is_active' => true, 'exchange_rate' => 0.385000],

            // African currencies
            ['code' => 'ZAR', 'name' => 'South African Rand', 'symbol' => 'R', 'is_active' => true, 'exchange_rate' => 18.500000],
            ['code' => 'EGP', 'name' => 'Egyptian Pound', 'symbol' => '£', 'is_active' => true, 'exchange_rate' => 30.900000],
            ['code' => 'NGN', 'name' => 'Nigerian Naira', 'symbol' => '₦', 'is_active' => true, 'exchange_rate' => 460.000000],
            ['code' => 'KES', 'name' => 'Kenyan Shilling', 'symbol' => 'KSh', 'is_active' => true, 'exchange_rate' => 149.000000],

            // Latin American currencies
            ['code' => 'BRL', 'name' => 'Brazilian Real', 'symbol' => 'R$', 'is_active' => true, 'exchange_rate' => 5.000000],
            ['code' => 'MXN', 'name' => 'Mexican Peso', 'symbol' => '$', 'is_active' => true, 'exchange_rate' => 17.000000],
            ['code' => 'ARS', 'name' => 'Argentine Peso', 'symbol' => '$', 'is_active' => true, 'exchange_rate' => 350.000000],
            ['code' => 'CLP', 'name' => 'Chilean Peso', 'symbol' => '$', 'is_active' => true, 'exchange_rate' => 900.000000],
            ['code' => 'COP', 'name' => 'Colombian Peso', 'symbol' => '$', 'is_active' => true, 'exchange_rate' => 4000.000000],
            ['code' => 'PEN', 'name' => 'Peruvian Sol', 'symbol' => 'S/', 'is_active' => true, 'exchange_rate' => 3.700000],

            // European currencies (non-Euro)
            ['code' => 'PLN', 'name' => 'Polish Zloty', 'symbol' => 'zł', 'is_active' => true, 'exchange_rate' => 4.050000],
            ['code' => 'CZK', 'name' => 'Czech Koruna', 'symbol' => 'Kč', 'is_active' => true, 'exchange_rate' => 22.500000],
            ['code' => 'HUF', 'name' => 'Hungarian Forint', 'symbol' => 'Ft', 'is_active' => true, 'exchange_rate' => 355.000000],
            ['code' => 'RON', 'name' => 'Romanian Leu', 'symbol' => 'lei', 'is_active' => true, 'exchange_rate' => 4.600000],
            ['code' => 'BGN', 'name' => 'Bulgarian Lev', 'symbol' => 'лв', 'is_active' => true, 'exchange_rate' => 1.800000],
            ['code' => 'HRK', 'name' => 'Croatian Kuna', 'symbol' => 'kn', 'is_active' => true, 'exchange_rate' => 6.900000],
            ['code' => 'RSD', 'name' => 'Serbian Dinar', 'symbol' => 'дин', 'is_active' => true, 'exchange_rate' => 108.000000],

            // Other notable currencies
            ['code' => 'RUB', 'name' => 'Russian Ruble', 'symbol' => '₽', 'is_active' => true, 'exchange_rate' => 95.000000],
            ['code' => 'TRY', 'name' => 'Turkish Lira', 'symbol' => '₺', 'is_active' => true, 'exchange_rate' => 28.000000],
            ['code' => 'ILS', 'name' => 'Israeli New Shekel', 'symbol' => '₪', 'is_active' => true, 'exchange_rate' => 3.700000],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(
                ['code' => $currency['code']],
                $currency
            );
        }
    }
}

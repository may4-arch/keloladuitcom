<?php

namespace Database\Seeders;

use App\Models\Currencies;
use Illuminate\Database\Seeder;

class CurrenciesSeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            [
                'code' => 'IDR',
                'name' => 'Indonesian Rupiah',
                'symbol' => 'Rp',
                'symbol_position' => 'prefix',
                'decimal_places' => 0,
                'thousand_separator' => '.',
                'decimal_separator' => ',',
                'exchange_rate' => 1.000000,
                'is_base' => true,
                'is_active' => true,
            ],
            [
                'code' => 'USD',
                'name' => 'US Dollar',
                'symbol' => '$',
                'symbol_position' => 'prefix',
                'decimal_places' => 2,
                'thousand_separator' => ',',
                'decimal_separator' => '.',
                'exchange_rate' => 15850.000000,
                'is_base' => false,
                'is_active' => true,
            ],
        ];

        foreach ($currencies as $currency) {
            Currencies::updateOrCreate(
                ['code' => $currency['code']],
                $currency
            );
        }
    }
}
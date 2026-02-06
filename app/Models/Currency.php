<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'code', 'name', 'symbol', 'exchange_rate', 'is_base', 'is_active', 'last_updated_at'
    ];

    protected $casts = [
        'exchange_rate' => 'decimal:6',
        'is_base' => 'boolean',
        'is_active' => 'boolean',
        'last_updated_at' => 'datetime',
    ];

    public function clients(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Client::class, 'currency', 'code');
    }

    public function invoices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Invoice::class, 'currency', 'code');
    }

    public function convertTo($amount, $targetCurrency): float|int
    {
        $baseCurrency = static::where('is_base', true)->first();

        if ($this->code === $targetCurrency) {
            return $amount;
        }

        // Convert to base currency first
        $baseAmount = $this->is_base ? $amount : $amount / $this->exchange_rate;

        // If target is base currency, return
        if ($targetCurrency === $baseCurrency->code) {
            return $baseAmount;
        }

        // Convert from base to target currency
        $targetCurrencyModel = static::where('code', $targetCurrency)->first();
        return $baseAmount * $targetCurrencyModel->exchange_rate;
    }

    public static function getBaseCurrency()
    {
        return static::where('is_base', true)->first();
    }
}

<?php

namespace App\Models;

use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use BelongsToOrganization, HasFactory, HasUuid;

    protected $fillable = [
        'organization_id',
        'invoice_id',
        'amount',
        'currency',
        'exchange_rate',
        'amount_in_base_currency',
        'payment_date',
        'method',
        'reference',
        'notes',
        // PayChangu fields
        'gateway',
        'status',
        'tx_ref',
        'gateway_reference',
        'channel',
        'gateway_response',
        'customer_details',
        'completed_at',
        'failed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'amount_in_base_currency' => 'decimal:2',
        'payment_date' => 'date',
        'gateway' => PaymentGateway::class,
        'status' => PaymentStatus::class,
        'gateway_response' => 'array',
        'customer_details' => 'array',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->exchange_rate)) {
                $currency = Currency::where('code', $payment->currency)->first();
                $payment->exchange_rate = $currency->exchange_rate ?? 1.0;
            }

            if (empty($payment->amount_in_base_currency)) {
                $baseCurrency = Currency::getBaseCurrency();
                $paymentCurrency = Currency::where('code', $payment->currency)->first();
                $payment->amount_in_base_currency = $paymentCurrency->convertTo($payment->amount, $baseCurrency->code);
            }
        });

        static::saved(function ($payment) {
            $payment->invoice->updateStatus();
        });

        static::deleted(function ($payment) {
            $payment->invoice->updateStatus();
        });
    }

    public function invoice(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function currency(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency', 'code');
    }
}

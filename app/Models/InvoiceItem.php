<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'invoice_id', 'description', 'quantity', 'unit_price', 'total'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->total = $item->quantity * $item->unit_price;
        });

        static::saved(function ($item) {
            // Use a direct query without global scopes to ensure we can find the invoice even when
            // organization global scopes are in effect during tests/factories.
            $invoice = \App\Models\Invoice::withoutGlobalScopes()->find($item->invoice_id);
            if ($invoice) {
                $invoice->calculateTotals();
            }
        });

        static::deleted(function ($item) {
            $invoice = \App\Models\Invoice::withoutGlobalScopes()->find($item->invoice_id);
            if ($invoice) {
                $invoice->calculateTotals();
            }
        });
    }

    public function invoice(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}

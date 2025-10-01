<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'organization_id', 'invoice_number', 'client_id', 'recurring_invoice_id', 'currency', 'exchange_rate',
        'issue_date', 'due_date', 'status', 'subtotal', 'tax_rate', 'tax_amount',
        'discount', 'total', 'amount_paid', 'notes', 'terms', 'sent_at', 'paid_at',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateInvoiceNumber();
            }

            if (empty($invoice->exchange_rate)) {
                $currency = Currency::where('code', $invoice->currency)->first();
                $invoice->exchange_rate = $currency->exchange_rate ?? 1.0;
            }
        });

        static::saved(function ($invoice) {
            $invoice->updateStatus();
        });
    }

    public function client(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function recurringInvoice(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(RecurringInvoice::class);
    }

    public function currency(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency', 'code');
    }

    public function calculateTotals(): static
    {
        /*$subtotal = $this->items->sum('total');
        $tax_amount = $subtotal * ($this->tax_rate / 100);
        $total = $subtotal + $tax_amount - $this->discount;

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $tax_amount,
            'total' => $total
        ]);*/

        $subtotal = $this->items->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });

        $tax_amount = $subtotal * ($this->tax_rate / 100);
        $total = $subtotal + $tax_amount - $this->discount;

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $tax_amount,
            'total' => $total,
        ]);

        return $this;
    }

    public function updateStatus(): void
    {
        $totalPaid = $this->payments->sum('amount');

        if ($totalPaid >= $this->total && $this->status !== 'paid') {
            $this->update([
                'status' => 'paid',
                'paid_at' => now(),
                'amount_paid' => $totalPaid,
            ]);
        } elseif ($totalPaid > 0 && $totalPaid < $this->total) {
            $this->update(['amount_paid' => $totalPaid]);
        }

        // Check if overdue
        if ($this->status === 'sent' && $this->due_date->isPast()) {
            $this->update(['status' => 'overdue']);
        }
    }

    public function getRemainingBalanceAttribute()
    {
        return $this->total - $this->payments->sum('amount');
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date->isPast() && in_array($this->status, ['sent', 'overdue']);
    }

    public function getFormattedTotalAttribute(): string
    {
        return $this->currency()->first()->symbol.number_format($this->total, 2);
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV-'.date('Y').'-';
        $lastInvoice = self::where('invoice_number', 'like', $prefix.'%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = intval(substr($lastInvoice->invoice_number, strlen($prefix)));

            return $prefix.str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        }

        return $prefix.'0001';
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
            'amount_paid' => $this->total,
        ]);
    }

    public function scopeOverdue($query)
    {
        /*return $query->where('due_date', '<', now())
            ->whereIn('status', ['sent', 'overdue']);*/

        return $query->where(function ($q) {
            $q->where('status', 'overdue')
                ->orWhere(function ($sq) {
                    $sq->where('status', 'sent')
                        ->where('due_date', '<', now());
                });
        });
    }

    public function scopeDueToday($query)
    {
        return $query->where('due_date', today())
            ->whereIn('status', ['sent', 'overdue']);
    }

    public function scopeDueSoon($query, $days = 7)
    {
        return $query->where('due_date', '<=', now()->addDays($days))
            ->whereIn('status', ['sent']);
    }
}

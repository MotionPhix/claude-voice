<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use BelongsToOrganization, HasFactory, HasUuid;

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
        'subtotal' => 'float',
        'tax_rate' => 'float',
        'tax_amount' => 'float',
        'discount' => 'float',
        'total' => 'float',
        'amount_paid' => 'float',
        'exchange_rate' => 'float',
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

        static::created(function ($invoice) {
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
        $this->amount_paid = $totalPaid;

        // Mark as paid if fully paid and total > 0
        if ($totalPaid >= $this->total && $this->total > 0 && $this->status !== 'paid') {
            $this->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
        }
        // Check if overdue, but only for sent invoices that aren't paid
        elseif ($this->status === 'sent' && $this->due_date?->isPast()) {
            $this->update(['status' => 'overdue']);
        }
    }

    public function getRemainingBalanceAttribute()
    {
        return $this->total - $this->payments->sum('amount');
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date && $this->due_date->isPast() && in_array($this->status, ['sent', 'overdue']);
    }

    public function getFormattedTotalAttribute(): string
    {
        $currency = $this->currency()->first();
        $symbol = $currency ? $currency->symbol : '$';
        return $symbol . number_format($this->total, 2);
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV-'.date('Y').'-';

        static $counter = null;

        if ($counter === null) {
            // initialize from DB max
            $startPos = strlen($prefix) + 1;

            $row = \DB::selectOne(
                "select max(cast(substr(invoice_number, $startPos) as UNSIGNED)) as max_num from invoices where invoice_number like ?",
                [$prefix.'%']
            );

            $max = $row->max_num ?? 0;

            $counter = (int) $max;
        }

        $counter++;

        // format
        $candidate = $prefix.str_pad($counter, 4, '0', STR_PAD_LEFT);

        // in the unlikely event it exists, keep incrementing
        while (self::withoutGlobalScopes()->where('invoice_number', $candidate)->exists()) {
            $counter++;
            $candidate = $prefix.str_pad($counter, 4, '0', STR_PAD_LEFT);
        }

        return $candidate;
    }

    public function markAsSent(): void
    {
        $this->status = 'sent';
        $this->sent_at = now();

        // Check if already overdue at time of sending
        if ($this->due_date->isPast()) {
            $this->status = 'overdue';
        }

        $this->saveQuietly(); // avoid triggering updateStatus
    }

    // Add method to duplicate an invoice with a new number
    public function duplicate(): static
    {
        $duplicate = $this->replicate([
            'invoice_number', 'status', 'sent_at', 'paid_at', 'amount_paid'
        ]);

        $duplicate->status = 'draft';
        $duplicate->issue_date = now()->toDateString();
        $duplicate->due_date = now()->addDays(30)->toDateString();
        $duplicate->save();

        // Copy items
        foreach ($this->items as $item) {
            $newItem = $item->replicate(['invoice_id']);
            $newItem->invoice_id = $duplicate->id;
            $newItem->save();
        }

        // Generate new sequential invoice number
        $duplicate->invoice_number = $this->generateInvoiceNumber();
        $duplicate->saveQuietly();

        return $duplicate;
    }

    public function markAsPaid(): void
    {
        $this->status = 'paid';
        $this->paid_at = now();
        $this->amount_paid = $this->total;
        $this->save();
    }

    public function scopeOverdue($query)
    {
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

<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecurringInvoice extends Model
{
    use BelongsToOrganization, HasFactory, HasUuid;

    protected $fillable = [
        'organization_id', 'name', 'client_id', 'currency', 'frequency', 'interval', 'start_date',
        'end_date', 'next_invoice_date', 'max_cycles', 'cycles_completed',
        'is_active', 'subtotal', 'tax_rate', 'tax_amount', 'discount', 'total',
        'notes', 'terms', 'payment_terms_days',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'next_invoice_date' => 'date',
        'is_active' => 'boolean',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function client(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RecurringInvoiceItem::class);
    }

    public function invoices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function currency(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency', 'code');
    }

    public function calculateTotals(): static
    {
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

    public function generateInvoice()
    {
        if (! $this->shouldGenerateInvoice()) {
            return null;
        }

        $dueDate = $this->next_invoice_date->addDays($this->payment_terms_days);

        $invoice = Invoice::create([
            'client_id' => $this->client_id,
            'recurring_invoice_id' => $this->id,
            'currency' => $this->currency,
            'issue_date' => $this->next_invoice_date,
            'due_date' => $dueDate,
            'tax_rate' => $this->tax_rate,
            'discount' => $this->discount,
            'notes' => $this->notes,
            'terms' => $this->terms,
        ]);

        // Copy items
        foreach ($this->items as $item) {
            $invoice->items()->create([
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
            ]);
        }

        $invoice->calculateTotals();

        // Update recurring invoice
        $this->updateNextInvoiceDate();
        $this->increment('cycles_completed');

        // Check if we should deactivate
        if ($this->max_cycles && $this->cycles_completed >= $this->max_cycles) {
            $this->update(['is_active' => false]);
        }

        if ($this->end_date && $this->next_invoice_date->greaterThan($this->end_date)) {
            $this->update(['is_active' => false]);
        }

        return $invoice;
    }

    public function shouldGenerateInvoice(): bool
    {
        return $this->is_active &&
            $this->next_invoice_date->isToday() &&
            (! $this->max_cycles || $this->cycles_completed < $this->max_cycles) &&
            (! $this->end_date || $this->next_invoice_date->lessThanOrEqualTo($this->end_date));
    }

    public function updateNextInvoiceDate(): void
    {
        $nextDate = $this->next_invoice_date;

        switch ($this->frequency) {
            case 'daily':
                $nextDate = $nextDate->addDays($this->interval);
                break;
            case 'weekly':
                $nextDate = $nextDate->addWeeks($this->interval);
                break;
            case 'monthly':
                $nextDate = $nextDate->addMonths($this->interval);
                break;
            case 'quarterly':
                $nextDate = $nextDate->addMonths(3 * $this->interval);
                break;
            case 'yearly':
                $nextDate = $nextDate->addYears($this->interval);
                break;
        }

        $this->update(['next_invoice_date' => $nextDate]);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDueToday($query)
    {
        return $query->where('next_invoice_date', today());
    }
}

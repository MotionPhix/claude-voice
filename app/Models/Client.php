<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'organization_id', 'name', 'email', 'phone', 'address', 'city', 'state',
        'postal_code', 'country', 'tax_number', 'currency', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function invoices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function recurringInvoices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RecurringInvoice::class);
    }

    public function currency(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency', 'code');
    }

    public function getTotalOwedAttribute()
    {
        return $this->invoices()
            ->whereIn('status', ['sent', 'overdue'])
            ->sum('total');
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state.' '.$this->postal_code,
            $this->country,
        ]);

        return implode(', ', $parts);
    }
}

<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceTemplate extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'view_path',
        'preview_image',
        'is_free',
        'price',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_free' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function organizations(): HasMany
    {
        return $this->hasMany(Organization::class, 'invoice_template_id');
    }

    public function scopeFree($query)
    {
        return $query->where('is_free', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePremium($query)
    {
        return $query->where('is_free', false);
    }

    public static function getDefault()
    {
        return static::where('slug', 'default')->first();
    }
}

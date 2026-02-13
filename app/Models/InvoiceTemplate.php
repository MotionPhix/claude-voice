<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceTemplate extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'organization_id',
        'name',
        'slug',
        'description',
        'view_path',
        'preview_image',
        'is_free',
        'price',
        'is_active',
        'settings',
        'design',
        'dynamic_fields',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'dynamic_fields' => 'array',
            'is_free' => 'boolean',
            'is_active' => 'boolean',
            'price' => 'decimal:2',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function organizationTemplates(): HasMany
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

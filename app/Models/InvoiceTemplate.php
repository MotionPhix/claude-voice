<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceTemplate extends Model
{
    use BelongsToOrganization, HasFactory;

    protected $fillable = [
        'organization_id', 'name', 'settings', 'is_default',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_default' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            if ($template->is_default) {
                static::where('organization_id', $template->organization_id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });

        static::updating(function ($template) {
            if ($template->is_default) {
                static::where('id', '!=', $template->id)
                    ->where('organization_id', $template->organization_id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });
    }

    public static function getDefault()
    {
        return static::where('is_default', true)->first();
    }
}

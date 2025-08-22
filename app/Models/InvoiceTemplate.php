<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'settings', 'is_default'
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
                static::where('is_default', true)->update(['is_default' => false]);
            }
        });

        static::updating(function ($template) {
            if ($template->is_default) {
                static::where('id', '!=', $template->id)
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

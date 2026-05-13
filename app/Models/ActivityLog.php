<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'event',
        'ip_address',
        'browser',
        'browser_version',
        'platform',
        'device_type',
        'user_agent',
        'url',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Label badge warna untuk event
     */
    public function getEventColorAttribute(): string
    {
        return match ($this->event) {
            'register' => 'success',
            'login'    => 'info',
            'logout'   => 'warning',
            default    => 'gray',
        };
    }

    /**
     * Label badge warna untuk device_type
     */
    public function getDeviceColorAttribute(): string
    {
        return match ($this->device_type) {
            'mobile'  => 'warning',
            'tablet'  => 'info',
            'desktop' => 'success',
            default   => 'gray',
        };
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'device_token', 'platform', 'user_agent', 'last_seen_at', 'is_active',
        'requester_name', 'requester_photo_url', 'purpose', 'sharing_enabled', 'sharing_revoked_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'is_active' => 'boolean',
        'sharing_enabled' => 'boolean',
        'sharing_revoked_at' => 'datetime',
    ];

    public function locations(): HasMany
    {
        return $this->hasMany(DeviceLocation::class);
    }

    public function latestLocation(): ?DeviceLocation
    {
        return $this->locations()->latest('recorded_at')->first();
    }
}

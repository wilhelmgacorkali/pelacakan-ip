<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'device_token', 'platform', 'user_agent', 'last_seen_at', 'is_active'
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'is_active' => 'boolean',
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

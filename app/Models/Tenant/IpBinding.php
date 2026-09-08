<?php

namespace App\Models\Tenant;

class IpBinding extends TenantModel
{
    protected $fillable = [
        'mac_address',
        'ip_address',
        'type',
        'comment',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public static function isBlocked(string $mac): bool
    {
        return self::where('mac_address', strtoupper(trim($mac)))
            ->where('type', 'blocked')
            ->exists();
    }

    public static function isBypassed(string $mac): bool
    {
        return self::where('mac_address', strtoupper(trim($mac)))
            ->where('type', 'bypassed')
            ->exists();
    }
}

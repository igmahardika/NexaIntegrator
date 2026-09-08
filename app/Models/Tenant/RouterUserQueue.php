<?php

namespace App\Models\Tenant;

class RouterUserQueue extends TenantModel
{
    protected $fillable = [
        'username',
        'password',
        'profile',
        'mac_address',
        'comment',
        'limit_uptime',
        'is_synced',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'is_synced'  => 'boolean',
            'synced_at'  => 'datetime',
        ];
    }
}

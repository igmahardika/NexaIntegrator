<?php

namespace App\Models\Tenant;

class AccessPoint extends TenantModel
{
    protected $fillable = [
        'name',
        'ip_address',
        'mac_address',
        'location_zone',
        'status',
        'last_seen_at',
        'connected_clients',
    ];

    protected function casts(): array
    {
        return [
            'last_seen_at'      => 'datetime',
            'connected_clients' => 'integer',
        ];
    }
}

<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Relations\HasMany;

class HotspotProfile extends TenantModel
{
    protected $fillable = [
        'name',
        'rate_limit',
        'shared_users',
        'uptime_limit',
        'quota_bytes',
        'price',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'shared_users' => 'integer',
            'uptime_limit' => 'integer',
            'quota_bytes'  => 'integer',
            'price'        => 'float',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(HotspotUser::class, 'profile_id');
    }
}

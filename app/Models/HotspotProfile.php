<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotspotProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'name',
        'display_name',
        'rate_limit',
        'shared_users',
        'session_timeout',
        'idle_timeout',
        'keepalive_timeout',
        'status_autorefresh',
        'transparent_proxy',
        'synced_to_router',
        'router_profile_id',
    ];

    protected function casts(): array
    {
        return [
            'shared_users'        => 'integer',
            'session_timeout'     => 'integer',
            'idle_timeout'        => 'integer',
            'keepalive_timeout'   => 'integer',
            'transparent_proxy'   => 'boolean',
            'synced_to_router'    => 'boolean',
        ];
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}

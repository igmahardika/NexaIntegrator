<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'name',
        'ip_address',
        'mac_address',
        'zone_location',
        'status',
        'last_latency_ms',
        'last_seen_at',
        'downtime_started_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'last_latency_ms'     => 'integer',
            'last_seen_at'        => 'datetime',
            'downtime_started_at' => 'datetime',
        ];
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function isOnline(): bool
    {
        return $this->status === 'online';
    }
}

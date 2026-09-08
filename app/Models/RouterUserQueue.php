<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouterUserQueue extends Model
{
    use HasUuids;

    protected $fillable = [
        'location_id',
        'username',
        'password',
        'profile',
        'mac_address',
        'limit_uptime',
        'comment',
        'is_synced',
        'synced_at',
    ];

    protected $casts = [
        'is_synced' => 'boolean',
        'synced_at' => 'datetime',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Enqueue a user for RouterOS synchronization.
     */
    public static function enqueueUser(
        string $locationId,
        string $username,
        string $password,
        string $profile = 'default',
        ?string $mac = null,
        ?string $comment = null,
        ?string $limitUptime = '02:00:00'
    ): self {
        return self::create([
            'location_id'  => $locationId,
            'username'     => $username,
            'password'     => $password,
            'profile'      => $profile,
            'mac_address'  => $mac ? strtoupper($mac) : null,
            'comment'      => $comment,
            'limit_uptime' => $limitUptime,
            'is_synced'    => false,
        ]);
    }
}

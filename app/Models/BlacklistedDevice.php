<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlacklistedDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'mac_address',
        'reason',
        'blocked_by',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public static function isBlocked(string $mac, ?string $locationId = null): bool
    {
        $mac = strtoupper(trim($mac));
        $query = static::where('mac_address', $mac);
        if ($locationId) {
            $query->where(function ($q) use ($locationId) {
                $q->whereNull('location_id')->orWhere('location_id', $locationId);
            });
        }
        return $query->exists();
    }
}

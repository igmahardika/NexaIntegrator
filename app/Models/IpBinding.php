<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpBinding extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'mac_address',
        'address',
        'to_address',
        'server',
        'type',
        'device_category',
        'comment',
        'router_binding_id',
        'synced_to_router',
    ];

    protected function casts(): array
    {
        return [
            'synced_to_router' => 'boolean',
        ];
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function scopeBypassed($query)
    {
        return $query->where('type', 'bypassed');
    }

    public function scopeBlocked($query)
    {
        return $query->where('type', 'blocked');
    }

    public function getFormattedCategoryAttribute(): string
    {
        return match($this->device_category) {
            'smart_tv' => 'Smart TV',
            'cctv'     => 'CCTV Camera',
            'printer'  => 'Printer / POS',
            'pos'      => 'Mesin EDC / POS',
            'console'  => 'Gaming Console',
            'iot'      => 'IoT Device',
            default    => 'Lainnya',
        };
    }
}

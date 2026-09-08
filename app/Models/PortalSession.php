<?php

namespace App\Models;

use App\Services\DeviceDetectionService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortalSession extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'location_id',
        'client_mac',
        'mac_vendor',
        'is_randomized_mac',
        'client_ip',
        'method',
        'identifier',
        'device_type',
        'device_brand',
        'device_model',
        'device_os',
        'browser',
        'user_agent',
        'login_time',
        'logout_time',
        'last_activity_at',
        'bytes_in',
        'bytes_out',
        'status',
        'acct_session_id',
    ];

    protected function casts(): array
    {
        return [
            'login_time'        => 'datetime',
            'logout_time'       => 'datetime',
            'last_activity_at'  => 'datetime',
            'bytes_in'          => 'integer',
            'bytes_out'         => 'integer',
            'is_randomized_mac' => 'boolean',
        ];
    }

    // ---- Relationships ----

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    // ---- Scopes ----

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('login_time', today());
    }

    // ---- Helpers ----

    public function getDurationAttribute(): ?string
    {
        if (!$this->login_time) return null;

        $end = $this->logout_time ?? now();
        $diff = $this->login_time->diffInMinutes($end);

        if ($diff >= 60) {
            $hours = floor($diff / 60);
            $mins  = $diff % 60;
            return "{$hours}j {$mins}m";
        }

        return "{$diff}m";
    }

    public function getTotalBytesAttribute(): int
    {
        return $this->bytes_in + $this->bytes_out;
    }

    public function getFormattedTrafficAttribute(): string
    {
        return static::formatBytes($this->total_bytes);
    }

    public function getFormattedInAttribute(): string
    {
        return static::formatBytes($this->bytes_in);
    }

    public function getFormattedOutAttribute(): string
    {
        return static::formatBytes($this->bytes_out);
    }

    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public static function logLogin(
        string $locationId,
        string $mac,
        string $ip,
        string $method,
        string $identifier,
        ?string $userAgent = null
    ): static {
        $ua = $userAgent ?: request()->userAgent();
        $device = DeviceDetectionService::detect($ua, $mac);

        return static::create([
            'location_id'       => $locationId,
            'client_mac'        => strtoupper($mac),
            'mac_vendor'        => $device['mac_vendor'],
            'is_randomized_mac' => $device['is_randomized_mac'],
            'client_ip'         => $ip,
            'method'            => $method,
            'identifier'        => $identifier,
            'device_type'       => $device['device_type'],
            'device_brand'      => $device['device_brand'],
            'device_model'      => $device['device_model'],
            'device_os'         => $device['device_os'],
            'browser'           => $device['browser'],
            'user_agent'        => substr($ua ?? '', 0, 500),
            'login_time'        => now(),
            'last_activity_at'  => now(),
            'status'            => 'active',
        ]);
    }
}

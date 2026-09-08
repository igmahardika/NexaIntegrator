<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PortalVoucher extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'location_id',
        'batch_name',
        'code',
        'duration_minutes',
        'rate_limit',
        'is_used',
        'used_by_mac',
        'used_at',
        'expired_at',
    ];

    protected function casts(): array
    {
        return [
            'is_used'          => 'boolean',
            'duration_minutes' => 'integer',
            'used_at'          => 'datetime',
            'expired_at'       => 'datetime',
        ];
    }

    // ---- Relationships ----

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    // ---- Scopes ----

    public function scopeAvailable($query)
    {
        return $query->where('is_used', false)
            ->where(function ($q) {
                $q->whereNull('expired_at')->orWhere('expired_at', '>', now());
            });
    }

    public function scopeBatch($query, string $batchName)
    {
        return $query->where('batch_name', $batchName);
    }

    // ---- Static Helpers ----

    /**
     * Generate a batch of voucher codes.
     */
    public static function generateBatch(
        string $batchName,
        int $count,
        int $durationMinutes,
        string $rateLimit,
        ?string $locationId = null,
        ?\Carbon\Carbon $expiredAt = null
    ): int {
        $created = 0;
        $attempts = 0;

        while ($created < $count && $attempts < $count * 3) {
            $code = strtoupper(Str::random(3) . '-' . Str::random(3) . '-' . Str::random(4));

            if (!static::where('code', $code)->exists()) {
                static::create([
                    'location_id'      => $locationId,
                    'batch_name'       => $batchName,
                    'code'             => $code,
                    'duration_minutes' => $durationMinutes,
                    'rate_limit'       => $rateLimit,
                    'is_used'          => false,
                    'expired_at'       => $expiredAt,
                ]);
                $created++;
            }

            $attempts++;
        }

        return $created;
    }

    public function isExpired(): bool
    {
        return $this->expired_at && $this->expired_at->isPast();
    }

    public function isValid(): bool
    {
        return !$this->is_used && !$this->isExpired();
    }

    public function markUsed(string $mac): void
    {
        $this->update([
            'is_used'    => true,
            'used_by_mac'=> strtoupper($mac),
            'used_at'    => now(),
        ]);
    }
}

<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class HotspotUser extends TenantModel
{
    public const AUTH_VOUCHER    = 'voucher';
    public const AUTH_MEMBER     = 'member';
    public const AUTH_WHATSAPP   = 'whatsapp';
    public const AUTH_SURVEY     = 'survey';
    public const AUTH_QUICK      = 'quick_click';
    public const AUTH_MAC        = 'mac_bypass';

    public const STATUS_READY    = 'ready';
    public const STATUS_ACTIVE   = 'active';
    public const STATUS_DEPLETED = 'depleted';
    public const STATUS_EXPIRED  = 'expired';
    public const STATUS_DISABLED = 'disabled';

    protected $fillable = [
        'identifier',
        'secret',
        'auth_method',
        'profile_id',
        'status',
        'simultaneous_use',
        'bound_mac',
        'uptime_limit',
        'data_limit_bytes',
        'used_uptime',
        'bytes_in',
        'bytes_out',
        'first_login_at',
        'last_login_at',
        'expires_at',
        'batch_name',
        'guest_metadata',
    ];

    protected function casts(): array
    {
        return [
            'guest_metadata'   => 'array',
            'first_login_at'   => 'datetime',
            'last_login_at'    => 'datetime',
            'expires_at'       => 'datetime',
            'simultaneous_use' => 'integer',
            'uptime_limit'     => 'integer',
            'data_limit_bytes' => 'integer',
            'used_uptime'      => 'integer',
            'bytes_in'         => 'integer',
            'bytes_out'        => 'integer',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(HotspotProfile::class, 'profile_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(HotspotSession::class, 'hotspot_user_id');
    }

    /**
     * Check if user is eligible to authenticate.
     */
    public function isUsable(): bool
    {
        if ($this->status === self::STATUS_DISABLED || $this->status === self::STATUS_DEPLETED) {
            return false;
        }

        if ($this->expires_at && now()->isAfter($this->expires_at)) {
            $this->update(['status' => self::STATUS_EXPIRED]);
            return false;
        }

        if ($this->uptime_limit && $this->used_uptime >= $this->uptime_limit) {
            $this->update(['status' => self::STATUS_DEPLETED]);
            return false;
        }

        if ($this->data_limit_bytes && ($this->bytes_in + $this->bytes_out) >= $this->data_limit_bytes) {
            $this->update(['status' => self::STATUS_DEPLETED]);
            return false;
        }

        return true;
    }

    /**
     * Verify credentials secret.
     */
    public function verifySecret(string $inputSecret): bool
    {
        if (empty($this->secret)) {
            return true; // Passwordless (e.g. voucher code only or 1-click)
        }

        // Support both hashed passwords and plain tokens/PINs
        if (str_starts_with($this->secret, '$2y$') || str_starts_with($this->secret, '$2a$')) {
            return Hash::check($inputSecret, $this->secret);
        }

        return hash_equals($this->secret, $inputSecret);
    }

    /**
     * Record a new login session.
     */
    public function recordLogin(string $mac, string $ip, ?string $userAgent = null): HotspotSession
    {
        $now = now();

        $updateData = [
            'status'        => self::STATUS_ACTIVE,
            'last_login_at' => $now,
        ];

        if (!$this->first_login_at) {
            $updateData['first_login_at'] = $now;

            // Calculate auto-expiry from first login if uptime limit specified
            if ($this->uptime_limit && !$this->expires_at) {
                $updateData['expires_at'] = $now->copy()->addSeconds($this->uptime_limit);
            }
        }

        if (($this->simultaneous_use ?? 1) <= 1 && empty($this->bound_mac)) {
            $updateData['bound_mac'] = strtoupper($mac);
        }

        $this->update($updateData);

        return $this->sessions()->create([
            'username'     => $this->identifier,
            'mac_address'  => strtoupper($mac),
            'ip_address'   => $ip,
            'auth_method'  => $this->auth_method,
            'session_start'=> $now,
            'status'       => 'active',
            'user_agent'   => $userAgent,
        ]);
    }

    /**
     * Generate a batch of vouchers.
     */
    public static function generateBatch(
        int $quantity,
        ?string $profileId = null,
        int $codeLength = 6,
        string $prefix = '',
        ?string $batchName = null,
        ?int $uptimeLimit = null,
        ?int $dataLimitBytes = null
    ): array {
        $batch = $batchName ?: 'Batch-' . now()->format('Ymd-His');
        $created = [];

        for ($i = 0; $i < $quantity; $i++) {
            $randomCode = strtoupper(Str::random($codeLength));
            $code = !empty($prefix) ? "{$prefix}-{$randomCode}" : $randomCode;

            // Ensure unique
            while (self::where('identifier', $code)->exists()) {
                $randomCode = strtoupper(Str::random($codeLength));
                $code = !empty($prefix) ? "{$prefix}-{$randomCode}" : $randomCode;
            }

            $user = self::create([
                'identifier'       => $code,
                'secret'           => null,
                'auth_method'      => self::AUTH_VOUCHER,
                'profile_id'       => $profileId,
                'status'           => self::STATUS_READY,
                'uptime_limit'     => $uptimeLimit,
                'data_limit_bytes' => $dataLimitBytes,
                'batch_name'       => $batch,
            ]);

            $created[] = $user;
        }

        return [
            'batch_name' => $batch,
            'count'      => count($created),
            'users'      => $created,
        ];
    }
}

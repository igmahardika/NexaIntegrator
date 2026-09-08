<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotspotSession extends TenantModel
{
    protected $fillable = [
        'hotspot_user_id',
        'username',
        'mac_address',
        'ip_address',
        'auth_method',
        'session_start',
        'session_end',
        'session_time',
        'bytes_in',
        'bytes_out',
        'status',
        'terminate_cause',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'session_start' => 'datetime',
            'session_end'   => 'datetime',
            'session_time'  => 'integer',
            'bytes_in'      => 'integer',
            'bytes_out'     => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(HotspotUser::class, 'hotspot_user_id');
    }

    /**
     * Terminate the session cleanly.
     */
    public function closeSession(string $cause = 'user_request'): void
    {
        $now = now();
        $duration = $this->session_start ? $now->diffInSeconds($this->session_start) : 0;

        $this->update([
            'status'          => 'closed',
            'session_end'     => $now,
            'session_time'    => $duration,
            'terminate_cause' => $cause,
        ]);
    }
}

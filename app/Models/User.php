<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'site_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // ---- Relationships ----

    public function site()
    {
        return $this->belongsTo(Location::class, 'site_id');
    }

    public function campaigns()
    {
        return $this->hasMany(SurveyCampaign::class, 'advertiser_id');
    }

    // ---- Role & Access Helpers ----

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isSiteAdmin(): bool
    {
        return $this->role === 'site_admin';
    }

    public function isOperator(): bool
    {
        return in_array($this->role, ['operator', 'site_admin']);
    }

    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }

    public function isAdvertiser(): bool
    {
        return $this->role === 'advertiser';
    }

    /**
     * Determine if user is authorized to manage or view a specific site.
     *
     * @param \App\Models\Location|string|null $site
     */
    public function canAccessSite($site): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (empty($site)) {
            return false;
        }

        $targetId = $site instanceof Location ? $site->id : $site;
        return (string) $this->site_id === (string) $targetId;
    }
}

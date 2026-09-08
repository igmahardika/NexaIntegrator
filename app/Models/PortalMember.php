<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class PortalMember extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'username',
        'password',
        'full_name',
        'role',
        'rate_limit',
        'shared_users',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'is_active'     => 'boolean',
            'shared_users'  => 'integer',
            'last_login_at' => 'datetime',
        ];
    }

    // ---- Password Handling ----

    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] = Hash::needsRehash($value) ? Hash::make($value) : $value;
    }

    public function verifyPassword(string $plain): bool
    {
        return Hash::check($plain, $this->password);
    }

    // ---- Scopes ----

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ---- Helpers ----

    public function isVip(): bool
    {
        return $this->role === 'vip';
    }

    public function touchLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }
}

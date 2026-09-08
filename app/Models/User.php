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
        ];
    }

    // ---- Relationships ----

    public function campaigns()
    {
        return $this->hasMany(SurveyCampaign::class, 'advertiser_id');
    }

    // ---- Helpers ----

    public function isSuperadmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isAdvertiser(): bool
    {
        return $this->role === 'advertiser';
    }
}

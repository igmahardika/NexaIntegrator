<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyCampaign extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'advertiser_id',
        'title',
        'sponsor_name',
        'video_url',
        'ad_banner_path',
        'min_watch_duration',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active'          => 'boolean',
            'min_watch_duration' => 'integer',
            'start_date'         => 'date',
            'end_date'           => 'date',
        ];
    }

    // ---- Relationships ----

    public function advertiser()
    {
        return $this->belongsTo(User::class, 'advertiser_id');
    }

    public function questions()
    {
        return $this->hasMany(SurveyQuestion::class, 'campaign_id')->orderBy('order');
    }

    public function responses()
    {
        return $this->hasMany(SurveyResponse::class, 'campaign_id');
    }

    // ---- Scopes ----

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            });
    }

    // ---- Helpers ----

    public function hasVideo(): bool
    {
        return !empty($this->video_url);
    }

    public function hasBanner(): bool
    {
        return !empty($this->ad_banner_path);
    }

    public function isExpired(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }

    public function getTotalResponsesAttribute(): int
    {
        return $this->responses()->count();
    }

    public function getBannerUrlAttribute(): ?string
    {
        return $this->ad_banner_path ? asset('storage/' . $this->ad_banner_path) : null;
    }
}

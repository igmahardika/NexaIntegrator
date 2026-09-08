<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyResponse extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'campaign_id',
        'location_id',
        'client_mac',
        'client_ip',
        'answers',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'answers'    => 'array',
            'created_at' => 'datetime',
        ];
    }

    // ---- Relationships ----

    public function campaign()
    {
        return $this->belongsTo(SurveyCampaign::class, 'campaign_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    // ---- Static Helpers ----

    /**
     * Check if MAC address has already responded in the past 24 hours.
     */
    public static function hasRecentResponse(string $mac, string $campaignId): bool
    {
        return static::where('client_mac', strtoupper($mac))
            ->where('campaign_id', $campaignId)
            ->where('created_at', '>=', now()->subHours(24))
            ->exists();
    }
}

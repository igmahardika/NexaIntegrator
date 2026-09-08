<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyCampaign extends TenantModel
{
    protected $fillable = [
        'title',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function questions(): HasMany
    {
        return $this->hasMany(SurveyQuestion::class, 'campaign_id')->orderBy('order_index');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class, 'campaign_id');
    }
}

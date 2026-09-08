<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyQuestion extends TenantModel
{
    protected $fillable = [
        'campaign_id',
        'question',
        'type',
        'options',
        'order_index',
    ];

    protected function casts(): array
    {
        return [
            'options'     => 'array',
            'order_index' => 'integer',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(SurveyCampaign::class, 'campaign_id');
    }
}

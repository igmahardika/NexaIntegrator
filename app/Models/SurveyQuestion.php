<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyQuestion extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'campaign_id',
        'question_text',
        'question_type',
        'options',
        'order',
        'is_required',
    ];

    protected function casts(): array
    {
        return [
            'options'     => 'array',
            'order'       => 'integer',
            'is_required' => 'boolean',
        ];
    }

    // ---- Relationships ----

    public function campaign()
    {
        return $this->belongsTo(SurveyCampaign::class, 'campaign_id');
    }

    // ---- Helpers ----

    public function isChoice(): bool
    {
        return in_array($this->question_type, ['single_choice', 'multiple_choice']);
    }

    public function isRating(): bool
    {
        return $this->question_type === 'rating';
    }

    public function isText(): bool
    {
        return $this->question_type === 'text';
    }
}

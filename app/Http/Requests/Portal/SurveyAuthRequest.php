<?php

namespace App\Http\Requests\Portal;

use Illuminate\Foundation\Http\FormRequest;

class SurveyAuthRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mac'         => ['required', 'string', 'max:17', 'regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/'],
            'ip'          => ['required', 'string', 'max:45'],
            'location_id' => ['required', 'string', 'exists:locations,id'],
            'campaign_id' => ['required', 'string', 'exists:survey_campaigns,id'],
            'answers'     => ['required', 'array'],
        ];
    }
}

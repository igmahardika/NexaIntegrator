<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, ['superadmin', 'advertiser']);
    }

    public function rules(): array
    {
        return [
            'title'               => ['required', 'string', 'max:255'],
            'sponsor_name'        => ['nullable', 'string', 'max:255'],
            'video_url'           => ['nullable', 'url', 'max:500'],
            'min_watch_duration'  => ['nullable', 'integer', 'min:0', 'max:600'],
            'start_date'          => ['nullable', 'date'],
            'end_date'            => ['nullable', 'date', 'after_or_equal:start_date'],
            'advertiser_id'       => ['nullable', 'exists:users,id'],
            'is_active'           => ['boolean'],
            'ad_banner'           => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'questions'           => ['nullable', 'array'],
            'questions.*.text'    => ['required_with:questions', 'string', 'max:500'],
            'questions.*.type'    => ['required_with:questions', 'in:single_choice,multiple_choice,text,rating'],
            'questions.*.options' => ['nullable', 'array'],
            'questions.*.required'=> ['nullable', 'boolean'],
        ];
    }
}

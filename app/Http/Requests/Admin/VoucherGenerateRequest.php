<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class VoucherGenerateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isSuperAdmin();
    }

    public function rules(): array
    {
        return [
            'batch_name'       => ['required', 'string', 'max:100'],
            'count'            => ['required', 'integer', 'min:1', 'max:500'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'rate_limit'       => ['required', 'string', 'max:20'],
            'location_id'      => ['nullable', 'exists:locations,id'],
            'expired_at'       => ['nullable', 'date', 'after:now'],
        ];
    }
}

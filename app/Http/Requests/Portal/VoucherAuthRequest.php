<?php

namespace App\Http\Requests\Portal;

use Illuminate\Foundation\Http\FormRequest;

class VoucherAuthRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public captive portal access
    }

    public function rules(): array
    {
        return [
            'mac'         => ['required', 'string', 'max:17', 'regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/'],
            'ip'          => ['required', 'string', 'max:45'],
            'location_id' => ['required', 'string', 'exists:locations,id'],
            'code'        => ['required', 'string', 'max:30'],
        ];
    }
}

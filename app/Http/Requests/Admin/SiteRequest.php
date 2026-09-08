<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isSuperAdmin();
    }

    public function rules(): array
    {
        return [
            'name'                 => ['required', 'string', 'max:100'],
            'customer_name'        => ['nullable', 'string', 'max:150'],
            'contact_email'        => ['nullable', 'email', 'max:100'],
            'contact_phone'        => ['nullable', 'string', 'max:50'],
            'business_type'        => ['required', 'string', 'in:cafe,hotel,retail,coworking,office,other'],
            'address'              => ['nullable', 'string', 'max:500'],
            'active_template'      => ['nullable', 'string', 'max:50'],
            'router_ip'            => ['nullable', 'ip'],
            'router_port'          => ['nullable', 'integer', 'between:1,65535'],
            'router_user'          => ['nullable', 'string', 'max:100'],
            'router_password'      => ['nullable', 'string', 'max:255'],
            'dns_name'             => ['nullable', 'string', 'max:255'],
            'is_active'            => ['boolean'],
            'max_active_devices'   => ['nullable', 'integer', 'min:1'],
            'bandwidth_limit_mbps' => ['nullable', 'integer', 'min:1'],
        ];
    }
}

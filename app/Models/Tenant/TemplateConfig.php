<?php

namespace App\Models\Tenant;

class TemplateConfig extends TenantModel
{
    protected $fillable = [
        'template_id',
        'title',
        'subtitle',
        'welcome_message',
        'terms_conditions',
        'logo_path',
        'background_path',
        'primary_color',
        'accent_color',
        'enabled_methods',
        'custom_css',
    ];

    protected function casts(): array
    {
        return [
            'enabled_methods' => 'array',
        ];
    }
}

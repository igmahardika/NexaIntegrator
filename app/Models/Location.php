<?php

namespace App\Models;

use App\Services\RadiusService;
use App\Services\TemplateRegistryService;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Location extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'customer_name',
        'contact_email',
        'contact_phone',
        'business_type',
        'notes',
        'slug',
        'address',
        'router_ip',
        'router_port',
        'router_user',
        'router_password',
        'dns_name',
        'is_active',

        // Template Configuration
        'active_template',
        'template_config',

        // RADIUS Configuration
        'radius_enabled',
        'radius_server_ip',
        'radius_auth_port',
        'radius_acct_port',
        'radius_secret',
        'radius_nas_id',
        'radius_coa_port',
        'default_rate_limit',
        'default_session_timeout',

        // Site Constraints
        'max_active_devices',
        'bandwidth_limit_mbps',
    ];

    protected function casts(): array
    {
        return [
            'is_active'               => 'boolean',
            'router_port'             => 'integer',
            'template_config'         => 'array',
            'radius_enabled'          => 'boolean',
            'radius_auth_port'        => 'integer',
            'radius_acct_port'        => 'integer',
            'radius_coa_port'         => 'integer',
            'default_session_timeout' => 'integer',
            'max_active_devices'      => 'integer',
            'bandwidth_limit_mbps'    => 'integer',
        ];
    }

    // ---- Encrypted Password Accessor/Mutator ----

    public function getRouterPasswordAttribute($value): ?string
    {
        if (empty($value)) return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value; // return raw if not encrypted (legacy)
        }
    }

    public function setRouterPasswordAttribute($value): void
    {
        $this->attributes['router_password'] = $value ? Crypt::encryptString($value) : null;
    }

    // ---- Relationships ----

    public function campaigns()
    {
        return $this->belongsToMany(SurveyCampaign::class, 'survey_responses', 'location_id', 'campaign_id');
    }

    public function responses()
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function vouchers()
    {
        return $this->hasMany(PortalVoucher::class);
    }

    public function sessions()
    {
        return $this->hasMany(PortalSession::class);
    }

    public function blacklistedDevices()
    {
        return $this->hasMany(BlacklistedDevice::class);
    }

    public function ipBindings()
    {
        return $this->hasMany(IpBinding::class);
    }

    public function hotspotProfiles()
    {
        return $this->hasMany(HotspotProfile::class);
    }

    public function accessPoints()
    {
        return $this->hasMany(AccessPoint::class);
    }

    // ---- Helpers ----

    public function getActiveCampaign(): ?SurveyCampaign
    {
        return SurveyCampaign::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            })
            ->latest()
            ->first();
    }

    /**
     * Get active merged template configuration, with optional template ID override for preview.
     */
    public function getResolvedTemplateConfig(?string $templateId = null): array
    {
        $templateId = $templateId ?: ($this->active_template ?: 'access-code');
        return TemplateRegistryService::resolveConfig($templateId, $this->template_config ?? []);
    }

    /**
     * Generate RouterOS RADIUS setup script.
     */
    public function getMikrotikRadiusScript(string $serverHost = ''): string
    {
        $service = new RadiusService($this);
        return $service->generateRouterOsScript($serverHost);
    }
}

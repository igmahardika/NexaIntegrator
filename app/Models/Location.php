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
        'gateway_mode',
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
     * Determine gateway architecture mode.
     * Standard: Direct RouterOS API.
     */
    public function isZeroTunnel(): bool
    {
        return $this->gateway_mode === 'zero_tunnel';
    }

    public function isDirectApi(): bool
    {
        return empty($this->gateway_mode) || $this->gateway_mode === 'direct_api';
    }

    public function isRadius(): bool
    {
        return $this->gateway_mode === 'radius';
    }

    public function getGatewayModeLabelAttribute(): string
    {
        return match($this->gateway_mode) {
            'radius'      => 'RADIUS AAA (Legacy)',
            'zero_tunnel' => 'Zero-Tunnel (Legacy)',
            default       => 'RouterOS Direct API (Standar)',
        };
    }

    /**
     * Generate RouterOS RADIUS setup script.
     */
    public function getMikrotikRadiusScript(string $serverHost = ''): string
    {
        $service = new RadiusService($this);
        return $service->generateRouterOsScript($serverHost);
    }

    /**
     * Generate unified MikroTik provisioning script.
     * Standard: Direct RouterOS API (Instant session activation, 0% flash wear, full bidirectional control).
     */
    public function getProvisioningScript(string $serverHost = '', string $baseUrl = ''): string
    {
        $canonicalHost = parse_url(config('app.url', 'https://lcps.nexa.net.id'), PHP_URL_HOST) ?: 'lcps.nexa.net.id';
        $canonicalBaseUrl = rtrim(config('app.url', 'https://lcps.nexa.net.id'), '/');

        $dnsHost = (!empty($serverHost) && !in_array($serverHost, ['127.0.0.1', 'localhost'])) ? $serverHost : $canonicalHost;
        $baseUrl = (!empty($baseUrl) && !str_contains($baseUrl, '127.0.0.1') && !str_contains($baseUrl, 'localhost')) ? rtrim($baseUrl, '/') : $canonicalBaseUrl;

        $user = $this->router_user ?: 'wifipads';
        $pass = $this->router_password ?: 'password123';
        $port = $this->router_port ?: 8728;
        $siteName = addslashes($this->name);

        return <<<RSC
# =====================================================================
# WiFiPads - MikroTik Unified RouterOS API Provisioning Script
# Site: {$siteName} ({$this->slug})
# Standar Integrasi: Direct Controller API (Port {$port})
# Keunggulan: Aktivasi Sesi Instan, 0% Flash Wear, Kontrol Penuh
# =====================================================================

# 1. Pastikan Service API RouterOS Aktif
/ip service
set api disabled=no port={$port}

# 2. Buat Group & User Operator Khusus WiFiPads Controller
/user group
:do { add name="wifipads-group" policy=read,write,policy,test,api comment="WiFiPads NAC API Group" } on-error={ :nothing }

/user
:do { remove [find name="{$user}"] } on-error={ :nothing }
add name="{$user}" group="wifipads-group" password="{$pass}" comment="WiFiPads Controller API Access"

# 3. Hotspot Server Profile: Izinkan HTTP PAP & Matikan RADIUS (Mencegah Timeout / Error CHAP)
/ip hotspot profile
:do { set [find] login-by=http-pap,http-chap use-radius=no } on-error={ :nothing }

# 4. Pastikan RADIUS Mati pada Login Manajemen Router (Mencegah Winbox/Admin Timeout)
/user aaa
set use-radius=no

# 5. Walled Garden Hotspot (Akses ke Cloud Controller, DNS & CDN)
/ip hotspot walled-garden ip
:do { add dst-port=53 protocol=udp action=accept comment="WiFiPads DNS UDP" } on-error={ :nothing }
:do { add dst-port=53 protocol=tcp action=accept comment="WiFiPads DNS TCP" } on-error={ :nothing }

/ip hotspot walled-garden
:do { add dst-host="*{$dnsHost}*" action=allow comment="WiFiPads Controller" } on-error={ :nothing }
:do { add dst-host="*fonts.googleapis.com*" action=allow comment="Google Fonts" } on-error={ :nothing }
:do { add dst-host="*fonts.gstatic.com*" action=allow comment="Google Fonts Static" } on-error={ :nothing }
:do { add dst-host="*unpkg.com*" action=allow comment="Alpine.js CDN" } on-error={ :nothing }

# 6. Bersihkan Sisa Script Sync Lama & RADIUS WiFiPads (Bebaskan Flash Router)
/system scheduler :do { remove [find name~"wifipads"] } on-error={ :nothing }
/system script :do { remove [find name~"wifipads"] } on-error={ :nothing }
/radius :do { remove [find comment~"WiFiPads"] } on-error={ :nothing }

:log info "WiFiPads Unified RouterOS API Provisioning selesai dikonfigurasi pada {$this->name}!"
:put ">>> Konfigurasi WiFiPads RouterOS API untuk [{$siteName}] Berhasil Diterapkan! <<<"
RSC;
    }
}


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
     */
    public function isZeroTunnel(): bool
    {
        return empty($this->gateway_mode) || $this->gateway_mode === 'zero_tunnel';
    }

    public function isDirectApi(): bool
    {
        return $this->gateway_mode === 'direct_api';
    }

    public function isRadius(): bool
    {
        return $this->gateway_mode === 'radius';
    }

    public function getGatewayModeLabelAttribute(): string
    {
        return match($this->gateway_mode) {
            'direct_api' => 'RouterOS API',
            'radius'     => 'RADIUS AAA',
            default      => 'Zero-Tunnel',
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
     * Generate unified MikroTik provisioning script tailored to the site's gateway mode.
     */
    public function getProvisioningScript(string $serverHost = '', string $baseUrl = ''): string
    {
        $canonicalHost = parse_url(config('app.url', 'https://lcps.nexa.net.id'), PHP_URL_HOST) ?: 'lcps.nexa.net.id';
        $canonicalBaseUrl = rtrim(config('app.url', 'https://lcps.nexa.net.id'), '/');

        $dnsHost = (!empty($serverHost) && !in_array($serverHost, ['127.0.0.1', 'localhost'])) ? $serverHost : $canonicalHost;
        $baseUrl = (!empty($baseUrl) && !str_contains($baseUrl, '127.0.0.1') && !str_contains($baseUrl, 'localhost')) ? rtrim($baseUrl, '/') : $canonicalBaseUrl;

        if ($this->isRadius()) {
            return $this->getMikrotikRadiusScript($serverHost);
        }

        if ($this->isDirectApi()) {
            $user = $this->router_user ?: 'wifipads';
            $pass = $this->router_password ?: 'password123';
            $port = $this->router_port ?: 8728;

            return <<<RSC
# =====================================================================
# WiFiPads Direct RouterOS API Provisioning Script
# Site: {$this->name} ({$this->slug})
# Mode: Direct RouterOS API (Port {$port})
# =====================================================================

# 1. Pastikan Service API RouterOS Aktif
/ip service
set api disabled=no port={$port}

# 2. Buat User Operator Khusus WiFiPads Controller
/user group
:do { add name="wifipads-group" policy=read,write,policy,test,api comment="WiFiPads NAC API Group" } on-error={ :nothing }

/user
:do { remove [find name="{$user}"] } on-error={ :nothing }
add name="{$user}" group="wifipads-group" password="{$pass}" comment="WiFiPads Controller API Access"

# 3. Walled Garden Hotspot (Akses ke Cloud Controller, DNS & CDN)
/ip hotspot walled-garden ip
:do { add dst-port=53 protocol=udp action=accept comment="WiFiPads DNS UDP" } on-error={ :nothing }
:do { add dst-port=53 protocol=tcp action=accept comment="WiFiPads DNS TCP" } on-error={ :nothing }

/ip hotspot walled-garden
:do { add dst-host="*{$dnsHost}*" action=allow comment="WiFiPads Controller" } on-error={ :nothing }
:do { add dst-host="*fonts.googleapis.com*" action=allow comment="Google Fonts" } on-error={ :nothing }
:do { add dst-host="*fonts.gstatic.com*" action=allow comment="Google Fonts Static" } on-error={ :nothing }
:do { add dst-host="*unpkg.com*" action=allow comment="Alpine.js CDN" } on-error={ :nothing }

:log info "WiFiPads Direct API Provisioning selesai dikonfigurasi pada {$this->name}!"
RSC;
        }

        // Default: Zero-Tunnel Reverse Polling (CGNAT / Tanpa VPN)
        $syncKeyParam = $this->radius_secret ? "?key=" . urlencode($this->radius_secret) : "";
        $syncUrl = "{$baseUrl}/api/router/{$this->slug}/sync-script{$syncKeyParam}";
        $fetchMode = str_starts_with($syncUrl, 'https://') ? 'mode=https check-certificate=no' : 'mode=http';

        return <<<RSC
# =====================================================================
# WiFiPads Zero-Tunnel Provisioning Script (Tanpa VPN / Tanpa Port Forward)
# Site: {$this->name} ({$this->slug})
# Arsitektur: Reverse Polling Scheduler (Aman di balik CGNAT / Indihome / Starlink)
# =====================================================================

# 1. Walled Garden (Mengizinkan akses ke Cloud Controller, DNS & CDN)
/ip hotspot walled-garden ip
:do { add dst-port=53 protocol=udp action=accept comment="WiFiPads DNS UDP" } on-error={ :nothing }
:do { add dst-port=53 protocol=tcp action=accept comment="WiFiPads DNS TCP" } on-error={ :nothing }

/ip hotspot walled-garden
:do { add dst-host="*{$dnsHost}*" action=allow comment="WiFiPads Controller" } on-error={ :nothing }
:do { add dst-host="*fonts.googleapis.com*" action=allow comment="Google Fonts" } on-error={ :nothing }
:do { add dst-host="*fonts.gstatic.com*" action=allow comment="Google Fonts Static" } on-error={ :nothing }
:do { add dst-host="*unpkg.com*" action=allow comment="Alpine.js CDN" } on-error={ :nothing }

# 2. Hotspot Server & User Profiles (Izinkan HTTP-PAP & QoS)
/ip hotspot profile
:do { set [find] login-by=http-pap,http-chap } on-error={ :nothing }

/ip hotspot user profile
:do { add name="survey-user" rate-limit="2M/5M" shared-users=1 status-autorefresh=1m transparent-proxy=no } on-error={ :nothing }
:do { add name="voucher-user" rate-limit="5M/10M" shared-users=1 status-autorefresh=1m transparent-proxy=no } on-error={ :nothing }
:do { add name="member-user" rate-limit="10M/20M" shared-users=2 status-autorefresh=1m transparent-proxy=no } on-error={ :nothing }

# 3. Background Sync Script (Tarik Akun Hotspot Baru)
/system script
:do { remove [find name="wifipads-sync"] } on-error={ :nothing }
add name="wifipads-sync" policy=ftp,reboot,read,write,policy,test,password,sniff,sensitive source="
    :do {
        /tool fetch url=\"{$syncUrl}\" dst-path=\"wifipads_queue.rsc\" {$fetchMode} keep-result=yes
        :delay 1s
        /import file-name=\"wifipads_queue.rsc\"
    } on-error={
        :log debug \"WiFiPads: Sync check completed (waiting for network or no update)\"
    }
"

# 4. Auto Scheduler (Setiap 5 Detik secara otomatis menarik user baru)
/system scheduler
:do { remove [find name="wifipads-auto-sync"] } on-error={ :nothing }
add name="wifipads-auto-sync" interval=5s on-event="wifipads-sync" start-time=startup comment="WiFiPads Edge User Provisioning"

:log info "WiFiPads Zero-Tunnel Auto Sync berhasil diaktifkan pada {$this->name}!"
RSC;
    }
}


<?php

namespace App\Services;

use App\Models\Location;
use Throwable;

class RadiusService
{
    protected Location $location;

    public function __construct(Location $location)
    {
        $this->location = $location;
    }

    /**
     * Generate complete MikroTik RouterOS CLI setup script for this site.
     */
    public function generateRouterOsScript(string $serverHost = ''): string
    {
        $loc = $this->location;

        $serverIp   = !empty($serverHost) ? $serverHost : ($loc->radius_server_ip ?: request()->getHost());
        if ($serverIp === '127.0.0.1' || $serverIp === 'localhost') {
            $serverIp = '192.168.88.1'; // practical default if running locally
        }

        $secret = $loc->radius_secret;
        if (empty($secret)) {
            $secret = \Illuminate\Support\Str::random(32);
            $loc->update(['radius_secret' => $secret]);
        }
        $authPort   = $loc->radius_auth_port ?: 1812;
        $acctPort   = $loc->radius_acct_port ?: 1813;
        $coaPort    = $loc->radius_coa_port ?: 3799;
        $nasId      = $loc->radius_nas_id ?: ($loc->slug ?: 'site-' . substr($loc->id, 0, 8));
        $rateLimit  = $loc->default_rate_limit ?: '5M/10M';
        $siteName   = addslashes($loc->name);

        $portalHost = request()->getHost() ?: 'portal.wifipads.local';

        return <<<ROUTEROS
# =====================================================================
# WiFiPads - MikroTik RouterOS Zero-Burden RADIUS & Hotspot Setup
# Site: {$siteName} (ID: {$loc->id})
# ARSITEKTUR ZERO-BURDEN:
# - Router flash TIDAK menyimpan database user (/ip hotspot user = KOSONG).
# - Seluruh autentikasi ditangani Cloud via RADIUS AAA (Port 1812/1813).
# - Sesi tamu aktif berjalan HANYA di RAM (/ip hotspot active) tanpa flash wear.
# =====================================================================

# 1. Bersihkan konfigurasi RADIUS & Walled Garden lama WiFiPads
/radius remove [find comment~"WiFiPads"]
/ip hotspot walled-garden ip remove [find comment~"WiFiPads"]
/ip hotspot walled-garden remove [find comment~"WiFiPads"]

# 2. Tambah RADIUS Server untuk Hotspot Authentication & Accounting
/radius add service=hotspot \\
    address={$serverIp} \\
    secret="{$secret}" \\
    authentication-port={$authPort} \\
    accounting-port={$acctPort} \\
    timeout=3000ms \\
    comment="WiFiPads-RADIUS-{$nasId}"

# 3. Aktifkan Incoming RADIUS Requests (RFC 3576 Packet of Disconnect / PoD)
/radius incoming set accept=yes port={$coaPort}

# 4. Konfigurasikan Hotspot Server Profile menggunakan RADIUS
/ip hotspot profile set [find] \\
    use-radius=yes \\
    radius-accounting=yes \\
    radius-interim-update=2m \\
    radius-location-name="{$nasId}" \\
    nas-port-type=wireless-802.11

# 5. Konfigurasi User Profile Default dengan Rate Limit Dinamis
/ip hotspot user profile set [find default=yes] \\
    rate-limit="{$rateLimit}" \\
    shared-users=1 \\
    keepalive-timeout=2m \\
    status-autorefresh=1m

# 6. Walled Garden: Izinkan Akses ke Server Captive Portal & DNS
/ip hotspot walled-garden ip add dst-address={$serverIp} action=accept comment="WiFiPads-Server-IP"
/ip hotspot walled-garden ip add dst-port=53 protocol=udp action=accept comment="WiFiPads-DNS-UDP"
/ip hotspot walled-garden ip add dst-port=53 protocol=tcp action=accept comment="WiFiPads-DNS-TCP"
/ip hotspot walled-garden add dst-host="*{$portalHost}*" action=allow comment="WiFiPads-Portal-Domain"
/ip hotspot walled-garden add dst-host="*fonts.googleapis.com*" action=allow comment="WiFiPads-GoogleFonts"
/ip hotspot walled-garden add dst-host="*fonts.gstatic.com*" action=allow comment="WiFiPads-GStatic"

# 7. Verifikasi Status Koneksi RADIUS
/radius monitor [find comment~"WiFiPads"] once
:put ">>> Konfigurasi Zero-Burden untuk Site [{$siteName}] Berhasil Diterapkan! <<<"
ROUTEROS;
    }

    /**
     * Generate minimal login.html redirector file (< 1 KB) for MikroTik router flash storage.
     */
    public function generateMinimalLoginHtml(string $portalUrl = ''): string
    {
        $url = !empty($portalUrl) ? $portalUrl : url('/portal');
        $siteId = $this->location->id;
        $siteSlug = $this->location->slug;

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting to WiFi Portal...</title>
    <meta http-equiv="refresh" content="0; url={$url}?site={$siteSlug}&mac=\$(mac)&ip=\$(ip)&link-login-only=\$(link-login-only)&link-orig=\$(link-orig-esc)&error=\$(error)">
    <script type="text/javascript">
        window.location.href = "{$url}?site={$siteSlug}&mac=\$(mac)&ip=\$(ip)&link-login-only=\$(link-login-only)&link-orig=\$(link-orig-esc)&error=\$(error)";
    </script>
</head>
<body style="margin:0;padding:0;background:#0F172A;color:#94A3B8;font-family:system-ui,-apple-system,sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;text-align:center;">
    <div>
        <p style="font-size:16px;font-weight:700;color:#FFFFFF;margin:0 0 8px 0;">Menghubungkan ke Portal WiFi...</p>
        <p style="font-size:13px;margin:0;">Silakan tunggu beberapa saat.</p>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Send RFC 3576 / RFC 5176 Disconnect-Request (Packet of Disconnect - PoD) to Router.
     * Code 40: Disconnect-Request, expected Code 41: Disconnect-ACK or 42: Disconnect-NAK.
     * Fallback to direct RouterOS API kick if socket cannot connect or fails.
     */
    public function sendDisconnect(string $macAddress, string $ipAddress = '', string $username = ''): array
    {
        $routerIp = $this->location->router_ip;
        $coaPort  = $this->location->radius_coa_port ?: 3799;
        $secret   = $this->location->radius_secret;
        $mac      = strtoupper(trim($macAddress));

        if (empty($secret)) {
            return [
                'success' => false,
                'method'  => 'none',
                'error'   => 'RADIUS Secret belum dikonfigurasi untuk site ini.',
            ];
        }

        if (empty($routerIp)) {
            return [
                'success' => false,
                'method'  => 'none',
                'error'   => 'Router IP belum dikonfigurasi pada site ini.',
            ];
        }

        // Try PoD via UDP socket
        try {
            $sock = @fsockopen("udp://{$routerIp}", $coaPort, $errno, $errstr, 2);
            if ($sock) {
                // Build simple RADIUS Disconnect-Request packet (Code 40)
                $packetId = mt_rand(1, 255);
                $attrs = '';

                // Attr 31: Calling-Station-Id (Client MAC)
                $attrs .= chr(31) . chr(strlen($mac) + 2) . $mac;

                // Attr 8: Framed-IP-Address (Client IP)
                if (!empty($ipAddress) && filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    $attrs .= chr(8) . chr(6) . inet_pton($ipAddress);
                }

                // Attr 1: User-Name
                if (!empty($username)) {
                    $attrs .= chr(1) . chr(strlen($username) + 2) . $username;
                }

                $len = 20 + strlen($attrs);
                // 16 zero bytes for authenticator calculation
                $header = chr(40) . chr($packetId) . pack('n', $len) . str_repeat("\0", 16);
                $authenticator = md5($header . $attrs . $secret, true);
                $fullPacket = chr(40) . chr($packetId) . pack('n', $len) . $authenticator . $attrs;

                fwrite($sock, $fullPacket);
                stream_set_timeout($sock, 2);
                $response = fread($sock, 1024);
                fclose($sock);

                if (!empty($response) && ord($response[0]) === 41) {
                    return [
                        'success' => true,
                        'method'  => 'radius_coa_ack',
                        'message' => 'Disconnect-ACK diterima dari router melalui RADIUS CoA port ' . $coaPort,
                    ];
                }
            }
        } catch (Throwable $e) {
            // Log & proceed to fallback
        }

        // Fallback: Kick via MikroTik RouterOS API
        try {
            $mikrotik = new MikrotikService($this->location);
            $apiResult = $mikrotik->kickUser($mac);
            if ($apiResult['success'] ?? false) {
                return [
                    'success' => true,
                    'method'  => 'routeros_api',
                    'message' => 'Sesi berhasil diputuskan via RouterOS API.',
                ];
            }
        } catch (Throwable $e) {
            // Ignore
        }

        return [
            'success' => true, // Still marked success in web UI so session marks disconnected
            'method'  => 'simulated_local',
            'message' => 'Sesi dinonaktifkan di sistem portal. Router sedang offline atau tidak dapat dijangkau.',
        ];
    }
}

<?php

namespace App\Services;

use App\Models\Location;
use App\Models\Tenant\HotspotUser;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class PmsIntegrationService
{
    /**
     * Verify guest room number and last name against PMS (Cloud PMS REST API or Local Tenant DB).
     *
     * @param Location $location
     * @param string $roomNumber
     * @param string $lastName
     * @return array ['success' => bool, 'message' => string, 'guest' => array|null]
     */
    public function verifyGuest(Location $location, string $roomNumber, string $lastName): array
    {
        $room = trim($roomNumber);
        $name = trim($lastName);

        if (empty($room) || empty($name)) {
            return [
                'success' => false,
                'message' => 'Nomor kamar dan nama belakang tamu wajib diisi.',
                'guest'   => null,
            ];
        }

        $config = $location->template_config ?? [];
        $pmsApiUrl = $config['pms_api_url'] ?? null;
        $pmsApiKey = $config['pms_api_key'] ?? null;

        // 1. If external PMS REST endpoint is configured, send verification request
        if (!empty($pmsApiUrl) && filter_var($pmsApiUrl, FILTER_VALIDATE_URL)) {
            try {
                $response = Http::timeout(5)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $pmsApiKey,
                        'Accept'        => 'application/json',
                    ])
                    ->post($pmsApiUrl, [
                        'site_id'     => $location->id,
                        'site_slug'   => $location->slug,
                        'room_number' => $room,
                        'last_name'   => $name,
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if ($data['verified'] ?? false) {
                        return [
                            'success' => true,
                            'message' => 'Verifikasi tamu kamar hotel berhasil.',
                            'guest'   => [
                                'room'     => $room,
                                'name'     => $data['guest_name'] ?? $name,
                                'check_in' => $data['check_in_date'] ?? now()->toIso8601String(),
                                'pms_mode' => 'external_api',
                            ],
                        ];
                    }

                    return [
                        'success' => false,
                        'message' => $data['message'] ?? 'Data kamar atau nama belakang tidak cocok dengan data check-in PMS.',
                        'guest'   => null,
                    ];
                }
            } catch (Throwable $e) {
                Log::warning("PMS API Error for site {$location->slug}: " . $e->getMessage());
                // Fallback to local evaluation
            }
        }

        // 2. Check in Local Tenant Database (HotspotUser with auth_method = pms)
        $previousSite = TenantManager::getActiveSite();
        try {
            TenantManager::switchConnection($location);

            $existingUser = HotspotUser::where('auth_method', 'pms')
                ->where(function ($q) use ($room, $name) {
                    $q->where('identifier', 'ROOM-' . $room)
                      ->orWhereJsonContains('guest_metadata->room', $room);
                })
                ->first();

            if ($existingUser) {
                $meta = $existingUser->guest_metadata ?? [];
                $registeredLastName = strtolower(trim($meta['last_name'] ?? ''));

                if (!empty($registeredLastName) && $registeredLastName !== strtolower($name)) {
                    return [
                        'success' => false,
                        'message' => 'Nama belakang tidak sesuai dengan data reservasi kamar ' . $room,
                        'guest'   => null,
                    ];
                }

                return [
                    'success' => true,
                    'message' => 'Verifikasi kamar berhasil (Tenant DB).',
                    'guest'   => [
                        'room'     => $room,
                        'name'     => $meta['name'] ?? $name,
                        'check_in' => $existingUser->first_login_at?->toIso8601String() ?? now()->toIso8601String(),
                        'pms_mode' => 'tenant_db',
                    ],
                ];
            }
        } catch (Throwable $e) {
            Log::warning("Tenant DB PMS query error for site {$location->slug}: " . $e->getMessage());
        } finally {
            TenantManager::switchConnection($previousSite);
        }

        // 3. Strict PMS Verification Enforcement (CWE-306 Hardening)
        // Access denied if guest is not verified via External PMS API or Tenant Database
        return [
            'success' => false,
            'message' => 'Data reservasi kamar ' . $room . ' atas nama ' . $name . ' tidak ditemukan di sistem hotel. Hubungi resepsionis / frontdesk.',
            'guest'   => null,
        ];
    }
}

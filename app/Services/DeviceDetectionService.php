<?php

namespace App\Services;

class DeviceDetectionService
{
    /**
     * Common IEEE OUI prefixes (uppercase, 6 hex characters no colon).
     */
    protected static array $macVendors = [
        // Apple
        '0017F2' => 'Apple', '001C43' => 'Apple', '001CB3' => 'Apple', '001E52' => 'Apple',
        '002312' => 'Apple', '002500' => 'Apple', '002608' => 'Apple', '0026B0' => 'Apple',
        '286A8D' => 'Apple', '3C0754' => 'Apple', '3C22FB' => 'Apple', '40A6D9' => 'Apple',
        '64B0A6' => 'Apple', '705681' => 'Apple', '7C04D0' => 'Apple', '848E0C' => 'Apple',
        '88665A' => 'Apple', '907240' => 'Apple', 'A4C361' => 'Apple', 'B827EB' => 'Apple',
        'BC926B' => 'Apple', 'C82A14' => 'Apple', 'D0034B' => 'Apple', 'F01898' => 'Apple',
        'F4F15A' => 'Apple', 'FCFC48' => 'Apple',

        // Samsung
        '0012FB' => 'Samsung', '001599' => 'Samsung', '00166C' => 'Samsung', '001A8A' => 'Samsung',
        '002119' => 'Samsung', '002339' => 'Samsung', '002637' => 'Samsung', '1449E0' => 'Samsung',
        '183A2D' => 'Samsung', '2C0E3D' => 'Samsung', '30074D' => 'Samsung', '3423BA' => 'Samsung',
        '380195' => 'Samsung', '50B7C3' => 'Samsung', '5C3C27' => 'Samsung', '8C7712' => 'Samsung',
        '946372' => 'Samsung', 'A80600' => 'Samsung', 'AC5F3E' => 'Samsung', 'B407C7' => 'Samsung',
        'D0B33F' => 'Samsung', 'E47C23' => 'Samsung', 'F0EE10' => 'Samsung',

        // Xiaomi / Redmi / POCO
        '04CF8C' => 'Xiaomi', '185936' => 'Xiaomi', '286C07' => 'Xiaomi', '34CE00' => 'Xiaomi',
        '50642B' => 'Xiaomi', '584498' => 'Xiaomi', '640980' => 'Xiaomi', '7C1D19' => 'Xiaomi',
        '8CBEBE' => 'Xiaomi', '9C99A0' => 'Xiaomi', 'A086C6' => 'Xiaomi', 'C46E7B' => 'Xiaomi',

        // Huawei / Honor
        '001E10' => 'Huawei', '00259E' => 'Huawei', '00464B' => 'Huawei', '044F4C' => 'Huawei',
        '104780' => 'Huawei', '246968' => 'Huawei', '486276' => 'Huawei', '707B6C' => 'Huawei',

        // Oppo / OnePlus / Realme
        '24DF6A' => 'Oppo', '405CB8' => 'Oppo', '503C3B' => 'Oppo', '8C11CB' => 'Oppo',
        'C04F09' => 'Oppo', 'EC7971' => 'Oppo',

        // Vivo / iQOO
        '181283' => 'Vivo', '20B001' => 'Vivo', '444153' => 'Vivo', '685B35' => 'Vivo',
        'A45590' => 'Vivo', 'D41243' => 'Vivo',

        // Intel / PC / Laptop
        '0002B3' => 'Intel', '000347' => 'Intel', '001302' => 'Intel', '001500' => 'Intel',
        '0019D1' => 'Intel', '001E64' => 'Intel', '00216A' => 'Intel', '081196' => 'Intel',
        '3C6AA7' => 'Intel', '4851B7' => 'Intel', '7C214A' => 'Intel', '88708C' => 'Intel',

        // Google
        '001A11' => 'Google', '3C5AB4' => 'Google', '546009' => 'Google', '94EB2C' => 'Google',
        'F40304' => 'Google', 'F88FCA' => 'Google',

        // Asus
        '000C6E' => 'Asus', '0015F2' => 'Asus', '001BFC' => 'Asus', '10BF48' => 'Asus',
        '1C872C' => 'Asus', '2C4D54' => 'Asus', '6045CB' => 'Asus',

        // TP-Link / Networking
        '001D0F' => 'TP-Link', '14CF92' => 'TP-Link', '50C7BF' => 'TP-Link', 'E848B8' => 'TP-Link',
    ];

    /**
     * Perform full device identification from User-Agent and MAC address.
     */
    public static function detect(?string $userAgent, ?string $macAddress = null): array
    {
        $ua = $userAgent ?? '';
        $mac = strtoupper(str_replace([':', '-', '.'], '', $macAddress ?? ''));

        // 1. MAC Vendor & Randomized Check
        $macVendor = null;
        $isRandomized = false;

        if (strlen($mac) >= 6) {
            $prefix = substr($mac, 0, 6);
            $macVendor = static::$macVendors[$prefix] ?? null;

            // Check locally administered / private MAC bit (bit 1 of 1st octet is 1: 2, 6, A, E)
            $secondChar = substr($mac, 1, 1);
            if (in_array($secondChar, ['2', '6', 'A', 'E', 'a', 'e'])) {
                $isRandomized = true;
                if (!$macVendor) {
                    $macVendor = 'Private / Randomized MAC';
                }
            }
        }

        // 2. Operating System & Category Detection
        $os = 'Unknown';
        $category = 'mobile'; // default in captive portal context
        $brand = 'Unknown';
        $model = null;
        $browser = 'Unknown';

        // OS Detection
        if (preg_match('/iPhone|iPad|iPod/i', $ua)) {
            $os = preg_match('/iPad/i', $ua) ? 'iPadOS' : 'iOS';
            $category = preg_match('/iPad/i', $ua) ? 'tablet' : 'mobile';
            $brand = 'Apple';
            $model = preg_match('/iPad/i', $ua) ? 'Apple iPad' : 'Apple iPhone';
        } elseif (preg_match('/Macintosh|Mac OS X/i', $ua)) {
            $os = 'macOS';
            $category = 'desktop';
            $brand = 'Apple';
            $model = 'Apple Mac';
        } elseif (preg_match('/Android/i', $ua)) {
            $os = 'Android';
            $category = preg_match('/Tablet|Android(?!.*Mobile)/i', $ua) ? 'tablet' : 'mobile';
            
            // Extract Android Brand & Model
            $brandInfo = static::detectAndroidBrand($ua);
            $brand = $brandInfo['brand'];
            $model = $brandInfo['model'];
        } elseif (preg_match('/Windows NT|Windows/i', $ua)) {
            $os = 'Windows';
            $category = 'desktop';
            $brand = $macVendor && $macVendor !== 'Intel' && !str_contains($macVendor, 'MAC') ? $macVendor : 'PC / Laptop';
            $model = 'Windows PC';
        } elseif (preg_match('/CrOS/i', $ua)) {
            $os = 'ChromeOS';
            $category = 'desktop';
            $brand = 'Google / Chromebook';
        } elseif (preg_match('/Linux/i', $ua)) {
            $os = 'Linux';
            $category = 'desktop';
            $brand = 'Linux PC';
        }

        // If UA didn't yield brand but MAC vendor did, use MAC vendor
        if ($brand === 'Unknown' && $macVendor && !str_contains($macVendor, 'MAC')) {
            $brand = $macVendor;
        }

        // 3. Browser Detection
        if (preg_match('/CaptiveNetworkSupport/i', $ua)) {
            $browser = 'Apple CNA Assistant';
        } elseif (preg_match('/SamsungBrowser\/([0-9\.]+)/i', $ua, $m)) {
            $browser = 'Samsung Internet ' . explode('.', $m[1])[0];
        } elseif (preg_match('/Edg\/([0-9\.]+)/i', $ua, $m)) {
            $browser = 'Microsoft Edge ' . explode('.', $m[1])[0];
        } elseif (preg_match('/Chrome\/([0-9\.]+)/i', $ua, $m)) {
            $browser = 'Chrome ' . explode('.', $m[1])[0];
        } elseif (preg_match('/Firefox\/([0-9\.]+)/i', $ua, $m)) {
            $browser = 'Firefox ' . explode('.', $m[1])[0];
        } elseif (preg_match('/Safari/i', $ua) && preg_match('/Version\/([0-9\.]+)/i', $ua, $m)) {
            $browser = 'Safari ' . explode('.', $m[1])[0];
        }

        return [
            'device_type'      => $category,
            'device_brand'     => $brand,
            'device_model'     => $model ?: $brand,
            'device_os'        => $os,
            'browser'          => $browser,
            'mac_vendor'       => $macVendor ?: 'Generic / Unregistered',
            'is_randomized_mac'=> $isRandomized,
            'user_agent'       => $ua,
        ];
    }

    /**
     * Inspect Android User-Agent strings for specific brand indicators.
     */
    protected static function detectAndroidBrand(string $ua): array
    {
        // Samsung
        if (preg_match('/\b(SM-[A-Z0-9]+|GT-[A-Z0-9]+|SAMSUNG|Galaxy)\b/i', $ua, $m)) {
            return ['brand' => 'Samsung', 'model' => 'Samsung ' . $m[1]];
        }
        // Xiaomi / Redmi / POCO
        if (preg_match('/\b(Redmi[A-Z0-9 _-]+|POCO[A-Z0-9 _-]+|Mi [A-Z0-9]+|2[0-9]{3}[A-Z0-9]+|Xiaomi)\b/i', $ua, $m)) {
            return ['brand' => 'Xiaomi', 'model' => 'Xiaomi ' . $m[1]];
        }
        // Oppo
        if (preg_match('/\b(CPH[0-9]+|OPPO|Find [A-Z0-9]+|Reno[A-Z0-9 _-]+)\b/i', $ua, $m)) {
            return ['brand' => 'Oppo', 'model' => 'Oppo ' . $m[1]];
        }
        // Vivo / iQOO
        if (preg_match('/\b(V2[0-9]{3}[A-Z]*|vivo [A-Z0-9]+|iQOO[A-Z0-9 _-]+)\b/i', $ua, $m)) {
            return ['brand' => 'Vivo', 'model' => 'Vivo ' . $m[1]];
        }
        // Realme
        if (preg_match('/\b(RMX[0-9]+|Realme[A-Z0-9 _-]*)\b/i', $ua, $m)) {
            return ['brand' => 'Realme', 'model' => 'Realme ' . $m[1]];
        }
        // Google Pixel
        if (preg_match('/\b(Pixel [0-9a-zA-Z ]+)\b/i', $ua, $m)) {
            return ['brand' => 'Google', 'model' => $m[1]];
        }
        // Infinix
        if (preg_match('/\b(Infinix [A-Z0-9_-]+|X[0-9]{3,4}[A-Z]*)\b/i', $ua, $m)) {
            return ['brand' => 'Infinix', 'model' => 'Infinix ' . $m[1]];
        }
        // Tecno
        if (preg_match('/\b(TECNO [A-Z0-9_-]+)\b/i', $ua, $m)) {
            return ['brand' => 'Tecno', 'model' => $m[1]];
        }
        // Asus / ROG
        if (preg_match('/\b(ASUS_[A-Z0-9]+|ROG Phone[A-Z0-9 _-]*)\b/i', $ua, $m)) {
            return ['brand' => 'Asus', 'model' => $m[1]];
        }
        // Huawei / Honor
        if (preg_match('/\b(HUAWEI|HONOR|[A-Z]{3}-LX[0-9]+|[A-Z]{3}-AL[0-9]+)\b/i', $ua, $m)) {
            return ['brand' => 'Huawei', 'model' => 'Huawei ' . $m[1]];
        }

        return ['brand' => 'Android Device', 'model' => 'Generic Android'];
    }
}

<?php

namespace Tests\Feature;

use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalAuthMethodsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Location gateway mode defaults to direct_api.
     */
    public function test_location_defaults_to_direct_api(): void
    {
        $location = new Location();
        $this->assertTrue($location->isDirectApi());

        $location->gateway_mode = 'direct_api';
        $this->assertTrue($location->isDirectApi());

        $location->gateway_mode = 'zero_tunnel';
        $this->assertFalse($location->isDirectApi());
    }

    /**
     * Test provisioning script generates Direct RouterOS API setup.
     */
    public function test_provisioning_script_generates_direct_api_script(): void
    {
        $location = new Location([
            'name' => 'Test Venue',
            'slug' => 'test-venue',
            'dns_name' => 'wifi.nexa.id',
            'gateway_mode' => 'direct_api',
            'router_user' => 'wifipads',
            'router_port' => 8728,
        ]);

        $script = $location->getProvisioningScript('lcps.nexa.net.id');

        $this->assertStringContainsString('WiFiPads - MikroTik Unified RouterOS API Provisioning Script', $script);
        $this->assertStringContainsString('Standar Integrasi: Direct Controller API', $script);
        $this->assertStringContainsString('set api disabled=no port=8728', $script);
        $this->assertStringContainsString('add name="wifipads"', $script);
        $this->assertStringContainsString('login-by=http-pap,http-chap', $script);
        $this->assertStringContainsString('use-radius=no', $script);
        // Ensure legacy 5-second polling scheduler is absent
        $this->assertStringNotContainsString('wifipads_sync.rsc', $script);
    }

    /**
     * Test all 7 portal auth endpoints handle validation gracefully.
     */
    public function test_portal_auth_endpoints_validate_inputs(): void
    {
        // 1. Voucher
        $resVoucher = $this->postJson('/api/portal/voucher', []);
        $resVoucher->assertStatus(422)
            ->assertJsonValidationErrors(['code']);

        // 2. Button (1-Click / Quick)
        $resButton = $this->postJson('/api/portal/quick', []);
        $resButton->assertStatus(422)
            ->assertJsonValidationErrors(['mac', 'ip', 'location_id']);

        // 3. WhatsApp
        $resWa = $this->postJson('/api/portal/whatsapp', []);
        $resWa->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);

        // 4. Email
        $resEmail = $this->postJson('/api/portal/email', []);
        $resEmail->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        // 5. Member
        $resMember = $this->postJson('/api/portal/member', []);
        $resMember->assertStatus(422)
            ->assertJsonValidationErrors(['username', 'password']);

        // 6. Survey
        $resSurvey = $this->postJson('/api/portal/survey', []);
        $resSurvey->assertStatus(422);

        // 7. Hotel PMS
        $resHotel = $this->postJson('/api/portal/pms', []);
        $resHotel->assertStatus(422)
            ->assertJsonValidationErrors(['room_number', 'last_name']);
    }
}

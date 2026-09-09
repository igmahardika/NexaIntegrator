<?php

namespace Tests\Feature;

use Tests\TestCase;

class RedirectTest extends TestCase
{
    public function test_legacy_routes_redirect_permanently(): void
    {
        $response = $this->get('/locations');
        $response->assertStatus(301);
        $response->assertRedirect('/admin/sites');

        $response = $this->get('/vouchers');
        $response->assertStatus(301);
        $response->assertRedirect('/admin/hotspot-users');

        $response = $this->get('/members');
        $response->assertStatus(301);
        $response->assertRedirect('/admin/hotspot-users?tab=member');
    }
}

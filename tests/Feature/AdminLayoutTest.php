<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pages_render_successfully_and_have_valid_layout()
    {
        $user = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@wifipads.com',
            'password' => Hash::make('secret123'),
            'role' => 'superadmin',
            'is_active' => true,
        ]);

        $routes = [
            'admin.dashboard',
            'admin.sites.index',
            'admin.sites.create',
            'admin.profiles.index',
            'admin.radius.index',
            'admin.devices.index',
            'admin.campaigns.index',
            'admin.users.index',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($user)->get(route($route));
            $this->assertEquals(200, $response->getStatusCode(), "Route $route failed to render with 200");

            $content = $response->getContent();

            // Ensure the main layout flex wrapper contains the main content canvas
            $this->assertStringContainsString('id="site-picker-trigger"', $content, "Site picker should be present on $route");
            $this->assertStringContainsString('<main class="flex-1 overflow-y-auto p-6 bg-canvas">', $content, "Main element must be properly structured on $route");
        }
    }
}

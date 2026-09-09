<?php

namespace Tests\Feature;

use Tests\TestCase;

class PortalTemplateTest extends TestCase
{
    /**
     * Test that the captive portal page renders with HTTP 200 and has no blue topbar.
     */
    public function test_portal_renders_without_blue_topbar(): void
    {
        $response = $this->get('/portal');

        $response->assertStatus(200);
        $response->assertDontSee('nexa-top-bar');
        $response->assertDontSee('nexa-top-bar-close');
        $response->assertSee('nexa-portal-canvas');
        $response->assertSee('nexa-modal-card');
        $response->assertSee('nexa-brand-logo');
    }

    /**
     * Test that all 7 registered templates render successfully with HTTP 200.
     */
    public function test_all_seven_templates_render_successfully(): void
    {
        $templates = [
            'access-code',
            'username-password',
            'whatsapp-login',
            'button',
            'email',
            'hotel-pms',
            'question',
        ];

        foreach ($templates as $templateId) {
            $response = $this->get('/portal?preview_template=' . $templateId);
            $response->assertStatus(200);
            $response->assertDontSee('nexa-top-bar');
            $response->assertSee('nexa-card-inner');
        }
    }

    /**
     * Test access-code template contains necessary inputs and submit button.
     */
    public function test_access_code_template_contains_form_elements(): void
    {
        $response = $this->get('/portal?preview_template=access-code');

        $response->assertStatus(200);
        $response->assertSee('id="voucher-form"', false);
        $response->assertSee('id="voucher-code"', false);
        $response->assertSee('id="voucher-submit"', false);
        $response->assertSee('nexanet.id');
    }

    /**
     * Test button 1-click template contains quick submit button.
     */
    public function test_button_template_contains_quick_submit(): void
    {
        $response = $this->get('/portal?preview_template=button');

        $response->assertStatus(200);
        $response->assertSee('id="quick-form"', false);
        $response->assertSee('id="quick-submit"', false);
    }
}

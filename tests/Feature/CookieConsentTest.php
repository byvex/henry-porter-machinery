<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CookieConsentTest extends TestCase
{
    use RefreshDatabase;

    public function test_cookie_consent_banner_is_visible_without_consent_cookie(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('id="cookie-consent-banner"', false);
        $response->assertSee('Cookie preferences');
        $response->assertSee('Accept all');
        $response->assertSee('Reject non-essential');
        $response->assertSee('data-cookie-settings-trigger', false);
        $response->assertSee('Cookie Settings');
        $response->assertDontSee('display: none;');
    }

    public function test_cookie_consent_banner_is_hidden_with_consent_cookie(): void
    {
        $response = $this->withUnencryptedCookie('hpm_cookie_consent', 'accepted')
            ->get(route('home'));

        $response->assertOk();
        $response->assertSee('id="cookie-consent-banner"', false);
        $response->assertSee('display: none;', false);
    }
}

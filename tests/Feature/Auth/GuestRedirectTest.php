<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class GuestRedirectTest extends TestCase
{
    public function test_guest_non_json_get_request_to_protected_route_redirects_to_login(): void
    {
        $response = $this->get(route('settings.users.index'), ['Accept' => '*/*']);

        $response->assertRedirect('/login');
    }

    public function test_guest_non_json_post_request_to_protected_route_redirects_to_login(): void
    {
        $response = $this->post(route('settings.users.store'));

        $response->assertRedirect('/login');
    }

    public function test_guest_json_request_to_protected_route_returns_unauthorized(): void
    {
        $response = $this->postJson(route('settings.users.store'));

        $response->assertStatus(401);
    }

    public function test_guest_browser_navigation_to_protected_route_redirects_to_login(): void
    {
        $response = $this->get(route('settings.users.index'), ['Accept' => 'text/html']);

        $response->assertRedirect('/login');
    }
}

<?php

namespace FluxErp\Tests\Feature\Web\Portal;

class LoginTest extends PortalSetup
{
    public function test_login_as_authenticated_user(): void
    {
        $this->actingAs($this->user, 'address')->get($this->portalDomain . '/login')
            ->assertStatus(302)
            ->assertRedirect(route('portal.dashboard'));
    }

    public function test_login_no_path(): void
    {
        $this->get($this->portalDomain . '/')
            ->assertStatus(302)
            ->assertRedirect($this->portalDomain . '/login');
    }

    public function test_login_page(): void
    {
        $this->get($this->portalDomain . '/login')
            ->assertStatus(200);
    }
}

<?php

namespace FluxErp\Tests\Feature\Web\Portal;

use Illuminate\Foundation\Testing\DatabaseTransactions;

class LoginTest extends PortalSetup
{
    use DatabaseTransactions;

    public function test_login_page()
    {
        $this->get($this->portalDomain.'/login')
            ->assertStatus(200);
    }

    public function test_login_no_path()
    {
        $this->get($this->portalDomain.'/')
            ->assertStatus(302)
            ->assertRedirect($this->portalDomain.'/login');
    }

    public function test_login_as_authenticated_user()
    {
        $this->actingAs($this->user, 'address')->get($this->portalDomain.'/login')
            ->assertStatus(302)
            ->assertRedirect(route('portal.dashboard'));
    }
}

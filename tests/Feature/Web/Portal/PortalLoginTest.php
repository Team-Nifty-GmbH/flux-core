<?php

namespace FluxErp\Tests\Feature\Web\Portal;

use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PortalLoginTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_login_page()
    {
        $this->get('portal.localhost/login')
            ->assertStatus(200);
    }

    public function test_login_no_path()
    {
        $this->get('portal.localhost/')
            ->assertStatus(302)
            ->assertRedirect(route('portal.localhost/login'));
    }

    public function test_login_as_authenticated_user()
    {
        $this->actingAs($this->user, 'address')->get('portal.localhost/login')
            ->assertStatus(302)
            ->assertRedirect(route('portal.localhost/'));
    }
}

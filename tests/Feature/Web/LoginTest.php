<?php

namespace FluxErp\Tests\Feature\Web;

class LoginTest extends BaseSetup
{
    public function test_login_page()
    {
        $this->get('/login')
            ->assertStatus(200);
    }

    public function test_login_no_path()
    {
        $this->get('/')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_login_as_authenticated_user()
    {
        $this->actingAs($this->user, 'web')->get('/login')
            ->assertStatus(302)
            ->assertRedirect();
    }
}

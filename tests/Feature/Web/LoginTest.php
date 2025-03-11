<?php

namespace FluxErp\Tests\Feature\Web;

class LoginTest extends BaseSetup
{
    public function test_login_as_authenticated_user(): void
    {
        $this->actingAs($this->user, 'web')->get('/login')
            ->assertStatus(302)
            ->assertRedirect();
    }

    public function test_login_no_path(): void
    {
        $this->get('/')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_login_page(): void
    {
        $this->get('/login')
            ->assertStatus(200);
    }
}

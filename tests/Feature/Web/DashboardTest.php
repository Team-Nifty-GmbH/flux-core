<?php

namespace FluxErp\Tests\Feature\Web;

class DashboardTest extends BaseSetup
{
    public function test_dashboard_page()
    {
        $this->actingAs($this->user, 'web')->get('/')
            ->assertStatus(200);
    }

    public function test_dashboard_no_user()
    {
        $this->get('/')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }
}

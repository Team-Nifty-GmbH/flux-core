<?php

namespace FluxErp\Tests\Feature\Web;

class DashboardTest extends BaseSetup
{
    public function test_dashboard_no_user(): void
    {
        $this->get('/')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_dashboard_page(): void
    {
        $this->actingAs($this->user, 'web')->get('/')
            ->assertStatus(200);
    }
}

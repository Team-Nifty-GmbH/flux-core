<?php

namespace FluxErp\Tests\Feature\Web;

use Illuminate\Foundation\Testing\DatabaseTransactions;

class DashboardTest extends BaseSetup
{
    use DatabaseTransactions;

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

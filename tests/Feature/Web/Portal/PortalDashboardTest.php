<?php

namespace FluxErp\Tests\Feature\Web\Portal;

use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PortalDashboardTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_portal_dashboard_page()
    {
        // Todo: add route permission
        $this->user->assignRole('Super Admin');

        $this->actingAs($this->user, 'web')->get('portal.localhost/dashboard')
            ->assertStatus(200);
    }

    public function test_portal_dashboard_no_user()
    {
        $this->get('portal.localhost/dashboard')
            ->assertStatus(302)
            ->assertRedirect(route('portal.localhost/login'));
    }

    public function test_portal_dashboard_without_permission()
    {
        $this->actingAs($this->user, 'web')->get('portal.localhost/dashboard')
            ->assertStatus(403);
    }
}

<?php

namespace FluxErp\Tests\Feature\Web\Portal;

class DashboardTest extends PortalSetup
{
    public function test_portal_dashboard_page()
    {
        $this->actingAs($this->user, 'address')->get(route('portal.dashboard'))
            ->assertStatus(200);
    }

    public function test_portal_dashboard_no_user()
    {
        $this->get(route('portal.dashboard'))
            ->assertStatus(302)
            ->assertRedirect($this->portalDomain . '/login');
    }
}

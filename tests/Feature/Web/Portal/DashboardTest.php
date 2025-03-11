<?php

namespace FluxErp\Tests\Feature\Web\Portal;

class DashboardTest extends PortalSetup
{
    public function test_portal_dashboard_no_user(): void
    {
        $this->get(route('portal.dashboard'))
            ->assertStatus(302)
            ->assertRedirect($this->portalDomain . '/login');
    }

    public function test_portal_dashboard_page(): void
    {
        $this->actingAs($this->user, 'address')->get(route('portal.dashboard'))
            ->assertStatus(200);
    }
}

<?php

namespace FluxErp\Tests\Feature\Web\Portal;

use FluxErp\Models\Permission;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CalendarTest extends PortalSetup
{
    use DatabaseTransactions;

    public function test_portal_calendar_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('calendar.get', 'address'));

        $this->actingAs($this->user, 'address')->get(route('portal.calendar'))
            ->assertStatus(200);
    }

    public function test_portal_calendar_no_user()
    {
        $this->get(route('portal.calendar'))
            ->assertStatus(302)
            ->assertRedirect($this->portalDomain.'/login');
    }

    public function test_portal_calendar_without_permission()
    {
        Permission::findOrCreate('calendar.get', 'address');

        $this->actingAs($this->user, 'address')->get(route('portal.calendar'))
            ->assertStatus(403);
    }
}

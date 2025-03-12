<?php

namespace FluxErp\Tests\Feature\Web\Portal;

use FluxErp\Models\Permission;

class CalendarTest extends PortalSetup
{
    public function test_portal_calendar_no_user(): void
    {
        $this->get(route('portal.calendar'))
            ->assertStatus(302)
            ->assertRedirect($this->portalDomain . '/login');
    }

    public function test_portal_calendar_page(): void
    {
        $this->user->givePermissionTo(Permission::findOrCreate('calendar.get', 'address'));

        $this->actingAs($this->user, 'address')->get(route('portal.calendar'))
            ->assertStatus(200);
    }

    public function test_portal_calendar_without_permission(): void
    {
        Permission::findOrCreate('calendar.get', 'address');

        $this->actingAs($this->user, 'address')->get(route('portal.calendar'))
            ->assertStatus(403);
    }
}

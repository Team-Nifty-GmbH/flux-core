<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;

class CalendarsTest extends BaseSetup
{
    public function test_calendars_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('calendars.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/calendars')
            ->assertStatus(200);
    }

    public function test_calendars_no_user()
    {
        $this->get('/calendars')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_calendars_without_permission()
    {
        Permission::findOrCreate('calendars.get', 'web');

        $this->actingAs($this->user, 'web')->get('/calendars')
            ->assertStatus(403);
    }
}

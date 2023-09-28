<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CalendarsTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_calendars_page()
    {
        $this->user->givePermissionTo(Permission::findByName('calendars.get', 'web'));

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
        $this->actingAs($this->user, 'web')->get('/calendars')
            ->assertStatus(403);
    }
}

<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SettingsTicketTypesTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_settings_ticket_types_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('settings.ticket-types.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/settings/ticket-types')
            ->assertStatus(200);
    }

    public function test_settings_ticket_types_no_user()
    {
        $this->get('/settings/ticket-types')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_settings_ticket_types_without_permission()
    {
        Permission::findOrCreate('settings.ticket-types.get', 'web');

        $this->actingAs($this->user, 'web')->get('/settings/ticket-types')
            ->assertStatus(403);
    }
}

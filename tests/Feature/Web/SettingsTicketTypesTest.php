<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;

class SettingsTicketTypesTest extends BaseSetup
{
    public function test_settings_ticket_types_no_user(): void
    {
        $this->get('/settings/ticket-types')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_settings_ticket_types_page(): void
    {
        $this->user->givePermissionTo(Permission::findOrCreate('settings.ticket-types.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/settings/ticket-types')
            ->assertStatus(200);
    }

    public function test_settings_ticket_types_without_permission(): void
    {
        Permission::findOrCreate('settings.ticket-types.get', 'web');

        $this->actingAs($this->user, 'web')->get('/settings/ticket-types')
            ->assertStatus(403);
    }
}

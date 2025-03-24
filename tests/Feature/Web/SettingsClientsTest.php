<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;

class SettingsClientsTest extends BaseSetup
{
    public function test_settings_clients_no_user(): void
    {
        $this->get('/settings/clients')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_settings_clients_page(): void
    {
        $this->user->givePermissionTo(Permission::findOrCreate('settings.clients.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/settings/clients')
            ->assertStatus(200);
    }

    public function test_settings_clients_without_permission(): void
    {
        Permission::findOrCreate('settings.clients.get', 'web');

        $this->actingAs($this->user, 'web')->get('/settings/clients')
            ->assertStatus(403);
    }
}

<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;

class SettingsTest extends BaseSetup
{
    public function test_settings_no_user(): void
    {
        $this->get('/settings/clients')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_settings_without_permission(): void
    {
        Permission::findOrCreate('settings.clients.get', 'web');

        $this->actingAs($this->user, guard: 'web')->get('/settings/clients')
            ->assertStatus(403);
    }
}

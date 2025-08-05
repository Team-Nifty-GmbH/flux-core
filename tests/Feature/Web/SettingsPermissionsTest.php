<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;

class SettingsPermissionsTest extends BaseSetup
{
    public function test_settings_permissions_no_user(): void
    {
        $this->get('/settings/permissions')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_settings_permissions_page(): void
    {
        $this->user->givePermissionTo(Permission::findOrCreate('settings.permissions.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/settings/permissions')
            ->assertStatus(200);
    }

    public function test_settings_permissions_without_permission(): void
    {
        Permission::findOrCreate('settings.permissions.get', 'web');

        $this->actingAs($this->user, 'web')->get('/settings/permissions')
            ->assertStatus(403);
    }
}

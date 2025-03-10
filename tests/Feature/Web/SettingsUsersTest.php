<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;

class SettingsUsersTest extends BaseSetup
{
    public function test_settings_users_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('settings.users.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/settings/users')
            ->assertStatus(200);
    }

    public function test_settings_users_no_user()
    {
        $this->get('/settings/users')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_settings_users_without_permission()
    {
        Permission::findOrCreate('settings.users.get', 'web');

        $this->actingAs($this->user, 'web')->get('/settings/users')
            ->assertStatus(403);
    }
}

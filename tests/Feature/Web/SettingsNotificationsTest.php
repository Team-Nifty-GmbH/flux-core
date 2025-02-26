<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;

class SettingsNotificationsTest extends BaseSetup
{
    public function test_settings_notifications_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('settings.notifications.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/settings/notifications')
            ->assertStatus(200);
    }

    public function test_settings_notifications_no_user()
    {
        $this->get('/settings/notifications')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_settings_notifications_without_permission()
    {
        Permission::findOrCreate('settings.notifications.get', 'web');

        $this->actingAs($this->user, 'web')->get('/settings/notifications')
            ->assertStatus(403);
    }
}

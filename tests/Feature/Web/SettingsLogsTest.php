<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;

class SettingsLogsTest extends BaseSetup
{
    public function test_settings_logs_no_user(): void
    {
        $this->get('/settings/logs')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_settings_logs_page(): void
    {
        $this->user->givePermissionTo(Permission::findOrCreate('settings.logs.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/settings/logs')
            ->assertStatus(200);
    }

    public function test_settings_logs_without_permission(): void
    {
        Permission::findOrCreate('settings.logs.get', 'web');

        $this->actingAs($this->user, 'web')->get('/settings/logs')
            ->assertStatus(403);
    }
}

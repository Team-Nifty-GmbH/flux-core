<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SettingsLogsTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_settings_logs_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('settings.logs.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/settings/logs')
            ->assertStatus(200);
    }

    public function test_settings_logs_no_user()
    {
        $this->get('/settings/logs')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_settings_logs_without_permission()
    {
        Permission::findOrCreate('settings.logs.get', 'web');

        $this->actingAs($this->user, 'web')->get('/settings/logs')
            ->assertStatus(403);
    }
}

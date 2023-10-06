<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SettingsPermissionsTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_settings_permissions_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('settings.permissions.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/settings/permissions')
            ->assertStatus(200);
    }

    public function test_settings_permissions_no_user()
    {
        $this->get('/settings/permissions')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_settings_permissions_without_permission()
    {
        $this->actingAs($this->user, 'web')->get('/settings/permissions')
            ->assertStatus(403);
    }
}

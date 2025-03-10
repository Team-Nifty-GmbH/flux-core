<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;

class SettingsOrderTypesTest extends BaseSetup
{
    public function test_settings_order_types_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('settings.order-types.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/settings/order-types')
            ->assertStatus(200);
    }

    public function test_settings_order_types_no_user()
    {
        $this->get('/settings/order-types')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_settings_order_types_without_permission()
    {
        Permission::findOrCreate('settings.order-types.get', 'web');

        $this->actingAs($this->user, 'web')->get('/settings/order-types')
            ->assertStatus(403);
    }
}

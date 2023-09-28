<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SettingsOrderTypesTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_settings_order_types_page()
    {
        $this->user->givePermissionTo(Permission::findByName('settings.order-types.get', 'web'));

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
        $this->actingAs($this->user, 'web')->get('/settings/order-types')
            ->assertStatus(403);
    }
}

<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;

class SettingsPriceListsTest extends BaseSetup
{
    public function test_settings_price_lists_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('settings.price-lists.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/settings/price-lists')
            ->assertStatus(200);
    }

    public function test_settings_price_lists_no_user()
    {
        $this->get('/settings/price-lists')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_settings_price_lists_without_permission()
    {
        Permission::findOrCreate('settings.price-lists.get', 'web');

        $this->actingAs($this->user, 'web')->get('/settings/price-lists')
            ->assertStatus(403);
    }
}

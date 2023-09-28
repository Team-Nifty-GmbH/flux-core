<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SettingsPriceListsTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_settings_price_lists_page()
    {
        $this->user->givePermissionTo(Permission::findByName('settings.price-lists.get', 'web'));

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
        $this->actingAs($this->user, 'web')->get('/settings/price-lists')
            ->assertStatus(403);
    }
}

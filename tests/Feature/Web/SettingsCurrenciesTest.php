<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SettingsCurrenciesTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_settings_currencies_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('settings.currencies.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/settings/currencies')
            ->assertStatus(200);
    }

    public function test_settings_currencies_no_user()
    {
        $this->get('/settings/currencies')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_settings_currencies_without_permission()
    {
        Permission::findOrCreate('settings.currencies.get', 'web');

        $this->actingAs($this->user, 'web')->get('/settings/currencies')
            ->assertStatus(403);
    }
}

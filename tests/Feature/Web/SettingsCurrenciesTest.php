<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;

class SettingsCurrenciesTest extends BaseSetup
{
    public function test_settings_currencies_no_user(): void
    {
        $this->get('/settings/currencies')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_settings_currencies_page(): void
    {
        $this->user->givePermissionTo(Permission::findOrCreate('settings.currencies.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/settings/currencies')
            ->assertStatus(200);
    }

    public function test_settings_currencies_without_permission(): void
    {
        Permission::findOrCreate('settings.currencies.get', 'web');

        $this->actingAs($this->user, 'web')->get('/settings/currencies')
            ->assertStatus(403);
    }
}

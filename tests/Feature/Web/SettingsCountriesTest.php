<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;

class SettingsCountriesTest extends BaseSetup
{
    public function test_settings_countries_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('settings.countries.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/settings/countries')
            ->assertStatus(200);
    }

    public function test_settings_countries_no_user()
    {
        $this->get('/settings/countries')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_settings_countries_without_permission()
    {
        Permission::findOrCreate('settings.countries.get', 'web');

        $this->actingAs($this->user, 'web')->get('/settings/countries')
            ->assertStatus(403);
    }
}

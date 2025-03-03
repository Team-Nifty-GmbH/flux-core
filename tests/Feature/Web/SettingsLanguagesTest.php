<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;

class SettingsLanguagesTest extends BaseSetup
{
    public function test_settings_languages_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('settings.languages.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/settings/languages')
            ->assertStatus(200);
    }

    public function test_settings_languages_no_user()
    {
        $this->get('/settings/languages')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_settings_languages_without_permission()
    {
        Permission::findOrCreate('settings.languages.get', 'web');

        $this->actingAs($this->user, 'web')->get('/settings/languages')
            ->assertStatus(403);
    }
}

<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;

class SettingsTranslationsTest extends BaseSetup
{
    public function test_settings_translations_no_user(): void
    {
        $this->get('/settings/translations')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_settings_translations_page(): void
    {
        $this->user->givePermissionTo(Permission::findOrCreate('settings.translations.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/settings/translations')
            ->assertStatus(200);
    }

    public function test_settings_translations_without_permission(): void
    {
        Permission::findOrCreate('settings.translations.get', 'web');

        $this->actingAs($this->user, 'web')->get('/settings/translations')
            ->assertStatus(403);
    }
}

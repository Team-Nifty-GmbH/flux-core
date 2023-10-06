<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SettingsTranslationsTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_settings_translations_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('settings.translations.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/settings/translations')
            ->assertStatus(200);
    }

    public function test_settings_translations_no_user()
    {
        $this->get('/settings/translations')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_settings_translations_without_permission()
    {
        $this->actingAs($this->user, 'web')->get('/settings/translations')
            ->assertStatus(403);
    }
}

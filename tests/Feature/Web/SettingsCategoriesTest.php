<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;

class SettingsCategoriesTest extends BaseSetup
{
    public function test_settings_categories_no_user(): void
    {
        $this->get('/settings/categories')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_settings_categories_page(): void
    {
        $this->user->givePermissionTo(
            Permission::findOrCreate('settings.categories.get', 'web')
        );

        $this->actingAs($this->user, 'web')->get('/settings/categories')
            ->assertStatus(200);
    }

    public function test_settings_categories_without_permission(): void
    {
        Permission::findOrCreate('settings.categories.get', 'web');

        $this->actingAs($this->user, 'web')->get('/settings/categories')
            ->assertStatus(403);
    }
}

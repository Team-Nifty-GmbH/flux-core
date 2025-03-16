<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;

class SettingsAdditionalColumnsTest extends BaseSetup
{
    public function test_settings_additional_columns_no_user(): void
    {
        $this->get('/settings/additional-columns')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_settings_additional_columns_page(): void
    {
        $this->user->givePermissionTo(
            Permission::findOrCreate('settings.additional-columns.get', 'web')
        );

        $this->actingAs($this->user, 'web')->get('/settings/additional-columns')
            ->assertStatus(200);
    }

    public function test_settings_additional_columns_without_permission(): void
    {
        Permission::findOrCreate('settings.additional-columns.get', 'web');

        $this->actingAs($this->user, 'web')->get('/settings/additional-columns')
            ->assertStatus(403);
    }
}

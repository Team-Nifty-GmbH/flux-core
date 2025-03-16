<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;

class SettingsDiscountGroupsTest extends BaseSetup
{
    public function test_settings_discount_groups_no_user(): void
    {
        $this->get('/settings/discount-groups')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_settings_discount_groups_page(): void
    {
        $this->user->givePermissionTo(Permission::findOrCreate('settings.discount-groups.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/settings/discount-groups')
            ->assertStatus(200);
    }

    public function test_settings_discount_groups_without_permission(): void
    {
        Permission::findOrCreate('settings.discount-groups.get', 'web');

        $this->actingAs($this->user, 'web')->get('/settings/discount-groups')
            ->assertStatus(403);
    }
}

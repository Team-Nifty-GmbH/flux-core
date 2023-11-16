<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SettingsDiscountGroupsTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_settings_discount_groups_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('settings.discount-groups.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/settings/discount-groups')
            ->assertStatus(200);
    }

    public function test_settings_discount_groups_no_user()
    {
        $this->get('/settings/discount-groups')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_settings_discount_groups_without_permission()
    {
        Permission::findOrCreate('settings.discount-groups.get', 'web');

        $this->actingAs($this->user, 'web')->get('/settings/discount-groups')
            ->assertStatus(403);
    }
}

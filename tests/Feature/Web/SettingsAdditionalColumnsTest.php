<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SettingsAdditionalColumnsTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_settings_additional_columns_page()
    {
        $this->user->givePermissionTo(Permission::findByName('settings.additional-columns.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/settings/additional-columns')
            ->assertStatus(200);
    }

    public function test_settings_additional_columns_no_user()
    {
        $this->get('/settings/additional-columns')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_settings_additional_columns_without_permission()
    {
        $this->actingAs($this->user, 'web')->get('/settings/additional-columns')
            ->assertStatus(403);
    }
}

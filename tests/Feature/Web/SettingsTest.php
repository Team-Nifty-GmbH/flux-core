<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SettingsTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_settings_no_user()
    {
        $this->get('/settings')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_settings_redirect_dashboard()
    {
        $this->user->givePermissionTo(Permission::findByName('settings.get', 'web'));

        $this->actingAs($this->user, guard: 'web')->get('/settings')
            ->assertStatus(301)
            ->assertRedirect(route('dashboard'));
    }
}

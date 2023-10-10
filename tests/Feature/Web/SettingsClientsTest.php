<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SettingsClientsTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_settings_clients_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('settings.clients.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/settings/clients')
            ->assertStatus(200);
    }

    public function test_settings_clients_no_user()
    {
        $this->get('/settings/clients')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_settings_clients_without_permission()
    {
        $this->actingAs($this->user, 'web')->get('/settings/clients')
            ->assertStatus(403);
    }
}

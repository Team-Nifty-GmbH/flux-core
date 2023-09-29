<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ContactsTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_dashboard_page()
    {
        // Todo: add route permission
        $this->user->assignRole('Super Admin');


        $this->actingAs($this->user, 'web')->get('portal.dashboard')
            ->assertStatus(200);
    }

    public function test_dashboard_no_user()
    {
        $this->get('/dashboard')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_dashboard_without_permission()
    {
        $this->actingAs($this->user, 'web')->get('portal.dashboard')
            ->assertStatus(403);
    }
}

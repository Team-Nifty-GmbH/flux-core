<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ContactsTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_contacts_page()
    {
        $this->user->givePermissionTo(Permission::findByName('contacts.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/contacts')
            ->assertStatus(200);
    }

    public function test_contacts_no_user()
    {
        $this->get('/contacts')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_contacts_without_permission()
    {
        $this->actingAs($this->user, 'web')->get('/contacts')
            ->assertStatus(403);
    }
}

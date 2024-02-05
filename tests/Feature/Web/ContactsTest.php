<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ContactsTest extends BaseSetup
{
    use DatabaseTransactions;

    private Contact $contact;

    protected function setUp(): void
    {
        parent::setUp();

        $paymentType = PaymentType::factory()->create([
            'client_id' => $this->dbClient->id,
        ]);

        $this->contact = Contact::factory()->create([
            'client_id' => $this->dbClient->id,
            'payment_type_id' => $paymentType->id,
        ]);

        Address::factory()->create([
            'client_id' => $this->dbClient->id,
            'contact_id' => $this->contact->id,
            'is_main_address' => true,
        ]);
    }

    public function test_contacts_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('contacts.get', 'web'));

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
        Permission::findOrCreate('contacts.get', 'web');

        $this->actingAs($this->user, 'web')->get('/contacts')
            ->assertStatus(403);
    }

    public function test_contacts_id_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('contacts.{id?}.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/contacts/' . $this->contact->id)
            ->assertStatus(200);
    }

    public function test_contacts_id_page_without_id()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('contacts.{id?}.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/contacts/0')
            ->assertStatus(404);
    }

    public function test_contacts_id_no_user()
    {
        $this->get('/contacts/' . $this->contact->id)
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_contacts_id_without_permission()
    {
        Permission::findOrCreate('contacts.{id?}.get', 'web');

        $this->actingAs($this->user, 'web')->get('/contacts/' . $this->contact->id)
            ->assertStatus(403);
    }

    public function test_contacts_id_contact_not_found()
    {
        $this->contact->delete();

        $this->user->givePermissionTo(Permission::findOrCreate('contacts.{id?}.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/contacts/' . $this->contact->id)
            ->assertStatus(404);
    }
}

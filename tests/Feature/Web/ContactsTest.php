<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Permission;

class ContactsTest extends BaseSetup
{
    private Contact $contact;

    protected function setUp(): void
    {
        parent::setUp();

        $paymentType = PaymentType::factory()
            ->hasAttached(factory: $this->dbClient, relationship: 'clients')
            ->create([
                'is_default' => false,
            ]);

        $this->contact = Contact::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'payment_type_id' => $paymentType->id,
        ]);

        Address::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'contact_id' => $this->contact->id,
            'is_main_address' => true,
        ]);
    }

    public function test_contacts_id_contact_not_found(): void
    {
        $this->contact->delete();

        $this->user->givePermissionTo(Permission::findOrCreate('contacts.{id?}.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/contacts/' . $this->contact->id)
            ->assertStatus(404);
    }

    public function test_contacts_id_no_user(): void
    {
        $this->get('/contacts/contacts/' . $this->contact->id)
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_contacts_id_page(): void
    {
        $this->user->givePermissionTo(Permission::findOrCreate('contacts.{id?}.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/contacts/contacts/' . $this->contact->id)
            ->assertStatus(200);
    }

    public function test_contacts_id_page_without_id(): void
    {
        $this->user->givePermissionTo(Permission::findOrCreate('contacts.{id?}.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/contacts/contacts/0')
            ->assertStatus(404);
    }

    public function test_contacts_id_without_permission(): void
    {
        Permission::findOrCreate('contacts.{id?}.get', 'web');

        $this->actingAs($this->user, 'web')->get('/contacts/contacts/' . $this->contact->id)
            ->assertStatus(403);
    }

    public function test_contacts_no_user(): void
    {
        $this->get('/contacts/contacts')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_contacts_page(): void
    {
        $this->user->givePermissionTo(Permission::findOrCreate('contacts.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/contacts/contacts')
            ->assertStatus(200);
    }

    public function test_contacts_without_permission(): void
    {
        Permission::findOrCreate('contacts.get', 'web');

        $this->actingAs($this->user, 'web')->get('/contacts/contacts')
            ->assertStatus(403);
    }
}

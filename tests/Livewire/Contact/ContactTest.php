<?php

namespace FluxErp\Tests\Livewire\Contact;

use FluxErp\Livewire\Contact\Contact;
use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Contact as ContactModel;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ContactTest extends TestCase
{
    private ContactModel $contact;

    protected function setUp(): void
    {
        parent::setUp();

        $client = Client::factory()->create([
            'is_default' => true,
        ]);
        $this->contact = ContactModel::factory()->create([
            'client_id' => $client->id,
        ]);

        $address = Address::factory()->create([
            'client_id' => $client->id,
            'contact_id' => $this->contact->id,
            'is_main_address' => true,
            'is_invoice_address' => true,
            'is_delivery_address' => true,
        ]);

        $this->contact->update([
            'main_address_id' => $address->id,
            'invoice_address_id' => $address->id,
            'delivery_address_id' => $address->id,
        ]);
    }

    public function test_can_delete_contact(): void
    {
        Livewire::test(Contact::class, ['id' => $this->contact->id])
            ->call('delete')
            ->assertHasNoErrors()
            ->assertRedirectToRoute('contacts.contacts');
    }

    public function test_renders_successfully(): void
    {
        Livewire::test(Contact::class, ['id' => $this->contact->id])
            ->assertStatus(200);
    }

    public function test_switch_tabs(): void
    {
        Livewire::test(Contact::class, ['id' => $this->contact->id])->cycleTabs();
    }
}

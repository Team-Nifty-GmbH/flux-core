<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Contact\Contact;
use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Contact as ContactModel;
use Livewire\Livewire;

beforeEach(function (): void {
    $client = Client::factory()->create([
        'is_default' => true,
    ]);
    $this->contact = ContactModel::factory()->create([
        'client_id' => $client->id,
    ]);

    Address::factory()->create([
        'client_id' => $client->id,
        'contact_id' => $this->contact->id,
        'is_main_address' => true,
        'is_invoice_address' => true,
        'is_delivery_address' => true,
    ]);
});

test('can delete contact', function (): void {
    Livewire::test(Contact::class, ['id' => $this->contact->id])
        ->call('delete')
        ->assertHasNoErrors()
        ->assertRedirectToRoute('contacts.contacts');
});

test('renders successfully', function (): void {
    Livewire::test(Contact::class, ['id' => $this->contact->id])
        ->assertStatus(200);
});

test('switch tabs', function (): void {
    Livewire::test(Contact::class, ['id' => $this->contact->id])->cycleTabs();
});

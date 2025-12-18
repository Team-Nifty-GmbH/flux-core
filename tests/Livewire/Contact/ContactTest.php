<?php

use FluxErp\Livewire\Contact\Contact;
use FluxErp\Models\Address;
use FluxErp\Models\Contact as ContactModel;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->contact = ContactModel::factory()->create([
        'tenant_id' => $this->dbTenant->id,
    ]);

    Address::factory()->create([
        'tenant_id' => $this->dbTenant->id,
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
        ->assertOk();
});

test('switch tabs', function (): void {
    Livewire::test(Contact::class, ['id' => $this->contact->id])->cycleTabs();
});

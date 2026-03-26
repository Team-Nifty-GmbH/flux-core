<?php

use FluxErp\Livewire\Widgets\Address as AddressView;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use Livewire\Livewire;

beforeEach(function (): void {
    $contact = Contact::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $this->address = Address::factory()->create([
        'contact_id' => $contact->id,
        'tenant_id' => $this->dbTenant->getKey(),
    ]);
});

test('renders successfully', function (): void {
    Livewire::test(AddressView::class, ['modelId' => $this->address->id])
        ->assertOk();
});

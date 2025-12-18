<?php

use FluxErp\Livewire\Address\Comments;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use Livewire\Livewire;

beforeEach(function (): void {
    $contact = Contact::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $this->address = Address::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->id,
    ]);
});

test('renders successfully', function (): void {
    Livewire::test(Comments::class, ['modelId' => $this->address->id])
        ->assertOk();
});

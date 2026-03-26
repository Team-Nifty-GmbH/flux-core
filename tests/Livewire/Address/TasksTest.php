<?php

use FluxErp\Livewire\Address\Tasks;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $contact = Contact::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);
    $address = Address::factory()->create([
        'contact_id' => $contact->id,
        'tenant_id' => $this->dbTenant->getKey(),
        'can_login' => false,
        'is_active' => true,
    ]);

    Livewire::test(Tasks::class, ['modelId' => $address->id])
        ->assertOk();
});

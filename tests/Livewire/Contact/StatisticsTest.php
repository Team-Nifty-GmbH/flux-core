<?php

use FluxErp\Livewire\Contact\Statistics;
use FluxErp\Models\Contact;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $contact = Contact::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();

    Livewire::test(Statistics::class, ['contactId' => $contact->getKey()])
        ->assertOk();
});

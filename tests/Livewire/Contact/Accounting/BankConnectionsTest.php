<?php

use FluxErp\Livewire\Contact\Accounting\BankConnections;
use FluxErp\Models\Contact;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $contact = Contact::factory()->create();

    Livewire::test(BankConnections::class, ['contactId' => $contact->getKey()])
        ->assertOk();
});

<?php

use FluxErp\Livewire\Contact\Accounting\CreditAccounts;
use FluxErp\Models\Contact;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $contact = Contact::factory()->create();

    Livewire::test(CreditAccounts::class, ['contactId' => $contact->getKey()])
        ->assertOk();
});

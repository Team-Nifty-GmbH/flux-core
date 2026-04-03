<?php

use FluxErp\Livewire\Contact\Accounting\AllDiscounts;
use FluxErp\Models\Contact;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $contact = Contact::factory()->create();

    Livewire::test(AllDiscounts::class, ['contactId' => $contact->getKey()])
        ->assertOk();
});

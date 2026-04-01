<?php

use FluxErp\Livewire\Contact\Accounting\Discounts;
use FluxErp\Models\Contact;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $contact = Contact::factory()->create();

    Livewire::test(Discounts::class, ['contactId' => $contact->getKey()])
        ->assertOk();
});

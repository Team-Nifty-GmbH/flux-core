<?php

use FluxErp\Livewire\Contact\Accounting\DiscountGroups;
use FluxErp\Models\Contact;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $contact = Contact::factory()->create();

    Livewire::test(DiscountGroups::class, ['contactId' => $contact->getKey()])
        ->assertOk();
});

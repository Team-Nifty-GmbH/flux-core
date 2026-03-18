<?php

use FluxErp\Livewire\Contact\Tickets;
use FluxErp\Models\Contact;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $contact = Contact::factory()->create();

    Livewire::test(Tickets::class, ['contactId' => $contact->id])
        ->assertOk();
});

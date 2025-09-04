<?php

use FluxErp\Livewire\Address\Comments;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use Livewire\Livewire;

beforeEach(function (): void {
    $contact = Contact::factory()->create([
        'client_id' => $this->dbClient->getKey(),
    ]);

    $this->address = Address::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'contact_id' => $contact->id,
    ]);
});

test('renders successfully', function (): void {
    Livewire::test(Comments::class, ['modelId' => $this->address->id])
        ->assertOk();
});

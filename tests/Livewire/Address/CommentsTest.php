<?php

use FluxErp\Livewire\Address\Comments;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use Livewire\Livewire;

beforeEach(function (): void {
    $contact = Contact::factory()->create();

    $this->address = Address::factory()->create([
        'contact_id' => $contact->id,
    ]);
});

test('renders successfully', function (): void {
    Livewire::test(Comments::class, ['modelId' => $this->address->id])
        ->assertOk();
});

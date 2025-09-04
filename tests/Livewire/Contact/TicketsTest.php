<?php

use FluxErp\Livewire\Contact\Tickets;
use FluxErp\Models\Contact;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->contact = Contact::factory()->create([
        'client_id' => $this->dbClient->getKey(),
    ]);
});

test('renders successfully', function (): void {
    Livewire::test(Tickets::class, ['contactId' => $this->contact->id])
        ->assertStatus(200);
});

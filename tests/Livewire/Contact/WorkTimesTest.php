<?php

use FluxErp\Livewire\Contact\WorkTimes;
use FluxErp\Models\Contact;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $contact = Contact::factory()->create([
        'client_id' => $this->dbClient->getKey(),
    ]);

    Livewire::test(WorkTimes::class, ['contactId' => $contact->id])
        ->assertOk();
});

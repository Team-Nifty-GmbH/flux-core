<?php

use FluxErp\Livewire\Contact\Contact as ContactView;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->contact = Contact::factory()->create([
        'client_id' => $this->dbClient->id,
    ]);

    Address::factory()->create([
        'client_id' => $this->dbClient->id,
        'contact_id' => $this->contact->id,
        'is_main_address' => true,
    ]);
});

test('renders successfully', function (): void {
    Livewire::test(ContactView::class, ['id' => $this->contact->id])
        ->assertOk();
});

<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Livewire\Address\Tasks;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $contact = Contact::factory()->create([
        'client_id' => $this->dbClient->getKey(),
    ]);
    $address = Address::factory()->create([
        'contact_id' => $contact->id,
        'client_id' => $this->dbClient->getKey(),
        'language_id' => $this->user->language_id,
        'can_login' => false,
        'is_active' => true,
    ]);

    Livewire::test(Tasks::class, ['modelId' => $address->id])
        ->assertStatus(200);
});

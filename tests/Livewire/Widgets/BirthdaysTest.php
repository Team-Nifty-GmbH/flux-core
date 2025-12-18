<?php

use FluxErp\Livewire\Widgets\Birthdays;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->contact = Contact::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $this->address = Address::factory()->create([
        'contact_id' => $this->contact->id,
        'tenant_id' => $this->dbTenant->getKey(),
        'date_of_birth' => now()->subYears(30),
        'is_active' => true,
    ]);
});

test('renders successfully', function (): void {
    Livewire::test(Birthdays::class)
        ->assertOk()
        ->assertCount('items', 1)
        ->assertSet('items.0.label', $this->address->name)
        ->assertSet('items.0.subLabel', $this->address->date_of_birth->isoFormat('L') . ' (30)');
});

<?php

use FluxErp\Actions\Lead\CreateLead;
use FluxErp\Actions\Lead\DeleteLead;
use FluxErp\Actions\Lead\UpdateLead;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Lead;
use FluxErp\Models\LeadState;

beforeEach(function (): void {
    $this->contact = Contact::factory()->create();
    $this->address = Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
    ]);
    $this->leadState = LeadState::factory()->create([
        'is_won' => false,
        'is_lost' => false,
    ]);
});

test('create lead', function (): void {
    $lead = CreateLead::make([
        'address_id' => $this->address->getKey(),
        'lead_state_id' => $this->leadState->getKey(),
        'name' => 'Big Deal',
    ])->validate()->execute();

    expect($lead)->toBeInstanceOf(Lead::class)
        ->name->toBe('Big Deal');
});

test('create lead requires address_id', function (): void {
    CreateLead::assertValidationErrors(
        ['name' => 'Test'],
        'address_id'
    );
});

test('update lead', function (): void {
    $lead = Lead::factory()->create([
        'address_id' => $this->address->getKey(),
        'lead_state_id' => $this->leadState->getKey(),
    ]);

    $updated = UpdateLead::make([
        'id' => $lead->getKey(),
        'name' => 'Bigger Deal',
        'start' => $lead->start ?? now()->toDateString(),
        'end' => $lead->end ?? now()->addMonth()->toDateString(),
        'lead_state_id' => $this->leadState->getKey(),
    ])->validate()->execute();

    expect($updated->name)->toBe('Bigger Deal');
});

test('delete lead', function (): void {
    $lead = Lead::factory()->create([
        'address_id' => $this->address->getKey(),
        'lead_state_id' => $this->leadState->getKey(),
    ]);

    expect(DeleteLead::make(['id' => $lead->getKey()])
        ->validate()->execute())->toBeTrue();
});

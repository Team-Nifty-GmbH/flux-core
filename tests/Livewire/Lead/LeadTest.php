<?php

use FluxErp\Livewire\Lead\Lead;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Lead as LeadModel;
use FluxErp\Models\LeadState;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function (): void {
    $contact = Contact::factory()->create();
    $this->address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
    ]);
    $this->leadState = LeadState::factory()->create();
});

test('renders successfully', function (): void {
    Livewire::test(Lead::class)
        ->assertOk();
});

test('renders with existing lead', function (): void {
    $lead = LeadModel::factory()->create([
        'address_id' => $this->address->getKey(),
        'lead_state_id' => $this->leadState->getKey(),
    ]);

    Livewire::test(Lead::class, ['id' => $lead])
        ->assertOk()
        ->assertSet('leadForm.id', $lead->getKey())
        ->assertSet('leadForm.name', $lead->name);
});

test('can save a lead', function (): void {
    $lead = LeadModel::factory()->create([
        'address_id' => $this->address->getKey(),
        'lead_state_id' => $this->leadState->getKey(),
    ]);

    $newName = Str::uuid()->toString();

    Livewire::test(Lead::class, ['id' => $lead])
        ->set('leadForm.name', $newName)
        ->call('save')
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('leads', [
        'id' => $lead->getKey(),
        'name' => $newName,
    ]);
});

test('save validation fails without name', function (): void {
    $lead = LeadModel::factory()->create([
        'address_id' => $this->address->getKey(),
        'lead_state_id' => $this->leadState->getKey(),
    ]);

    Livewire::test(Lead::class, ['id' => $lead])
        ->set('leadForm.name', '')
        ->call('save')
        ->assertReturned(false);
});

test('resetForm reloads lead data from database', function (): void {
    $lead = LeadModel::factory()->create([
        'address_id' => $this->address->getKey(),
        'lead_state_id' => $this->leadState->getKey(),
    ]);

    Livewire::test(Lead::class, ['id' => $lead])
        ->set('leadForm.name', 'Changed Name')
        ->call('resetForm')
        ->assertSet('leadForm.name', $lead->name);
});

test('lead form fills user_id with authenticated user', function (): void {
    $lead = LeadModel::factory()->create([
        'address_id' => $this->address->getKey(),
        'lead_state_id' => $this->leadState->getKey(),
    ]);

    Livewire::test(Lead::class, ['id' => $lead])
        ->assertSet('leadForm.user_id', $this->user->getKey());
});

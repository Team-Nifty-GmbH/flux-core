<?php

use FluxErp\Livewire\Contact\Leads;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Lead;
use FluxErp\Models\LeadState;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Leads::class)
        ->assertOk();
});

test('renders with contact id', function (): void {
    $contact = Contact::factory()->create();

    Livewire::test(Leads::class, ['contactId' => $contact->getKey()])
        ->assertOk()
        ->assertSet('contactId', $contact->getKey());
});

test('edit with null resets form and opens modal', function (): void {
    $contact = Contact::factory()->create();

    Livewire::test(Leads::class, ['contactId' => $contact->getKey()])
        ->call('edit', null)
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('leadForm.id', null)
        ->assertSet('leadForm.name', null)
        ->assertOpensModal();
});

test('edit with null sets address_id from contact main address', function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
    ]);
    $contact->update(['main_address_id' => $address->getKey()]);

    Livewire::test(Leads::class, ['contactId' => $contact->getKey()])
        ->call('edit', null)
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('leadForm.address_id', $address->getKey());
});

test('edit with id redirects to lead page', function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
    ]);

    $leadState = LeadState::factory()->create();
    $lead = Lead::factory()->create([
        'address_id' => $address->getKey(),
        'lead_state_id' => $leadState->getKey(),
    ]);

    Livewire::test(Leads::class, ['contactId' => $contact->getKey()])
        ->call('edit', $lead->getKey())
        ->assertRedirect(route('sales.lead.id', $lead->getKey()));
});

test('can create lead via save', function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
    ]);
    $leadState = LeadState::factory()->create();

    Livewire::test(Leads::class, ['contactId' => $contact->getKey()])
        ->call('edit', null)
        ->set('leadForm.name', 'Test Lead')
        ->set('leadForm.address_id', $address->getKey())
        ->set('leadForm.lead_state_id', $leadState->getKey())
        ->set('leadForm.probability_percentage', 50)
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertDatabaseHas('leads', [
        'name' => 'Test Lead',
        'address_id' => $address->getKey(),
        'lead_state_id' => $leadState->getKey(),
    ]);
});

test('save validation fails with missing required fields', function (): void {
    $contact = Contact::factory()->create();

    Livewire::test(Leads::class, ['contactId' => $contact->getKey()])
        ->call('edit', null)
        ->set('leadForm.name', null)
        ->set('leadForm.address_id', null)
        ->call('save')
        ->assertOk()
        ->assertReturned(false);
});

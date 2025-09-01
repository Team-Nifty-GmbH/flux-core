<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Lead;
use FluxErp\Models\LeadState;
use Livewire\Livewire;

test('lead list', function (): void {
    $lead = Lead::factory()->create();

    $contact = Contact::factory()->create([
        'client_id' => $this->dbClient->getKey(),
    ]);

    $address = Address::factory()->create([
        'contact_id' => $contact->id,
        'client_id' => $this->dbClient->getKey(),
    ]);

    $leadState = LeadState::factory()->create();

    Livewire::test($this->livewireComponent)
        ->datatableEdit($lead, 'sales.lead.id')
        ->datatableDelete($lead, $this)
        ->datatableCreate(
            'leadForm',
            Lead::factory()->make([
                'address_id' => $address->id,
                'lead_state_id' => $leadState->id,
            ])
                ->toArray());
});

test('renders successfully', function (): void {
    Livewire::test($this->livewireComponent)
        ->assertStatus(200);
});

<?php

namespace FluxErp\Tests\Livewire\Lead;

use FluxErp\Livewire\Lead\LeadList;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Lead;
use FluxErp\Models\LeadState;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class LeadListTest extends BaseSetup
{
    protected string $livewireComponent = LeadList::class;

    public function test_lead_list(): void
    {
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
            ->datatableCreate('leadForm', Lead::factory()->make([
                'address_id' => $address->id,
                'lead_state_id' => $leadState->id,
            ])->toArray());
    }

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

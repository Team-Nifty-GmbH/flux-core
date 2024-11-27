<?php

namespace FluxErp\Tests\Livewire\Widgets;

use FluxErp\Livewire\Widgets\Address as AddressView;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class AddressTest extends BaseSetup
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $contact = Contact::factory()->create([
            'client_id' => $this->dbClient->id,
        ]);

        $this->address = Address::factory()->create([
            'contact_id' => $contact->id,
            'client_id' => $this->dbClient->id,
        ]);
    }

    public function test_renders_successfully()
    {
        Livewire::test(AddressView::class, ['modelId' => $this->address->id])
            ->assertStatus(200);
    }
}

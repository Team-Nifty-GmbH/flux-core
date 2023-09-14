<?php

namespace FluxErp\Tests\Livewire;

use FluxErp\Livewire\Address\Address;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class AddressTest extends BaseSetup
{
    use DatabaseTransactions;

    protected \FluxErp\Models\Address $address;

    public function setUp(): void
    {
        parent::setUp();

        $contact = \FluxErp\Models\Contact::factory()->create([
            'client_id' => $this->dbClient->id,
        ]);

        $this->address = \FluxErp\Models\Address::factory()->create([
            'client_id' => $this->dbClient->id,
            'contact_id' => $contact->id,
            'is_main_address' => true,
        ]);
    }

    public function test_renders_successfully()
    {
        Livewire::test(Address::class, ['address' => $this->address->toArray()])
            ->assertStatus(200);
    }
}

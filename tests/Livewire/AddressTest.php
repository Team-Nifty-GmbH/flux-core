<?php

namespace FluxErp\Tests\Livewire;

use FluxErp\Livewire\Address\Address as AddressView;
use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class AddressTest extends TestCase
{
    use DatabaseTransactions;

    protected Address $address;

    public function setUp(): void
    {
        parent::setUp();

        $dbClient = Client::factory()->create();

        $contact = Contact::factory()->create([
            'client_id' => $dbClient->id,
        ]);

        $this->address = Address::factory()->create([
            'client_id' => $dbClient->id,
            'contact_id' => $contact->id,
            'is_main_address' => true,
        ]);
    }

    public function test_renders_successfully()
    {
        Livewire::test(AddressView::class, ['address' => $this->address->toArray()])
            ->assertStatus(200);
    }
}

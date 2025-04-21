<?php

namespace FluxErp\Tests\Feature\Factories;

use FluxErp\Models\Contact;
use FluxErp\Models\Pivots\AddressAddressTypeOrder;
use FluxErp\Tests\Feature\BaseSetup;

class AddressAddressTypeOrderFactoryTest extends BaseSetup
{

    private Contact $contact;

    protected function setUp(): void
    {
        parent::setUp();
        $this->contact = Contact::factory()->create([
            'client_id' => $this->dbClient->getKey(),
        ]);
    }
    public function test_address_address_type_order_factory_creates_valid_instance(): void
    {
        $pivot = AddressAddressTypeOrder::factory()
            ->forClient($this->dbClient, $this->contact->id)
            ->create();


        $this->assertInstanceOf(AddressAddressTypeOrder::class, $pivot);
    }

    public function test_relationships_are_correctly_set_up(): void
    {
        $pivot = AddressAddressTypeOrder::factory()
            ->forClient($this->dbClient, $this->contact->id)
            ->create();

    }
}

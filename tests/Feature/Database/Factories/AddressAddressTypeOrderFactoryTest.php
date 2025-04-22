<?php

namespace FluxErp\Tests\Feature\Factories;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\AddressType;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Pivots\AddressAddressTypeOrder;
use FluxErp\Models\PriceList;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AddressAddressTypeOrderFactoryTest extends BaseSetup
{
    private Address $address;

    private AddressType $addressType;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $contact = Contact::factory()->create([
            'client_id' => $this->dbClient->getKey(),
        ]);

        $this->address = Address::factory()->create([
            'company' => Str::uuid()->toString(),
            'client_id' => $this->dbClient->getKey(),
            'contact_id' => $contact->getKey(),
        ]);

        $priceList = PriceList::factory()->create();

        $currency = Currency::factory()->create([
            'is_default' => true,
        ]);

        $language = Language::factory()->create();

        $orderType = OrderType::factory()
            ->create([
                'print_layouts' => ['offer', 'invoice'],
                'client_id' => $this->dbClient->getKey(),
                'order_type_enum' => OrderTypeEnum::Order,
            ]);

        $paymentType = PaymentType::factory()
            ->hasAttached(factory: $this->dbClient, relationship: 'clients')
            ->create([
                'is_default' => false,
            ]);

        $this->order = Order::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'language_id' => $language->getKey(),
            'order_type_id' => $orderType->getKey(),
            'payment_type_id' => $paymentType->getKey(),
            'price_list_id' => $priceList->getKey(),
            'currency_id' => $currency->getKey(),
            'address_invoice_id' => $this->address->getKey(),
            'address_delivery_id' => $this->address->getKey(),
            'is_locked' => false,
        ]);

        $this->addressType = AddressType::factory()->create([
            'client_id' => $this->dbClient->id,
        ]);
    }

    public function test_address_address_type_order_factory_creates_valid_instance(): void
    {
        Model::withoutEvents(function (): void {
            $addressAddressTypeOrder = AddressAddressTypeOrder::factory()->create([
                'order_id' => $this->order->getKey(),
                'address_id' => $this->address->getKey(),
                'address_type_id' => $this->addressType->getKey(),
            ]);

            $this->assertInstanceOf(AddressAddressTypeOrder::class, $addressAddressTypeOrder);
        });
    }

    public function test_relationships_are_correctly_set_up(): void
    {
        Model::withoutEvents(function (): void {
            $addressAddressTypeOrder = AddressAddressTypeOrder::factory()->create([
                'order_id' => $this->order->getKey(),
                'address_id' => $this->address->getKey(),
                'address_type_id' => $this->addressType->getKey(),
            ]);

            $this->assertInstanceOf(Order::class, $addressAddressTypeOrder->order);

            $this->assertInstanceOf(Address::class, $addressAddressTypeOrder->address);

            $this->assertInstanceOf(AddressType::class, $addressAddressTypeOrder->addressType);
        });
    }
}

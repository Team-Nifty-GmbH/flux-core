<?php

namespace FluxErp\Tests\Livewire\Features;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Support\Livewire\SignatureLinkGenerator;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class SignatureLinkGeneratorTest extends TestCase
{
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $client = Client::factory()->create([
            'is_default' => true,
        ]);
        $currency = Currency::factory()->create([
            'is_default' => true,
        ]);
        $contact = Contact::factory()->create([
            'client_id' => $client->id,
        ]);
        $priceList = PriceList::factory()->create([
            'is_default' => true,
        ]);

        $paymentType = PaymentType::factory()
            ->hasAttached(factory: $client, relationship: 'clients')
            ->create([
                'is_default' => true,
            ]);

        $orderType = OrderType::factory()->create([
            'client_id' => $client->id,
            'order_type_enum' => OrderTypeEnum::Order->value,
        ]);

        $address = Address::factory()->create([
            'client_id' => $client->id,
            'contact_id' => $contact->id,
            'is_main_address' => true,
            'is_invoice_address' => true,
            'is_delivery_address' => true,
        ]);

        $this->order = Order::factory()->create([
            'client_id' => $client->id,
            'currency_id' => $currency->id,
            'address_invoice_id' => $address->id,
            'price_list_id' => $priceList->id,
            'payment_type_id' => $paymentType->id,
            'order_type_id' => $orderType->id,
        ]);
    }

    public function test_renders_successfully(): void
    {
        Livewire::test(SignatureLinkGenerator::class, ['modelType' => Order::class, 'modelId' => $this->order->id])
            ->assertStatus(200);
    }
}

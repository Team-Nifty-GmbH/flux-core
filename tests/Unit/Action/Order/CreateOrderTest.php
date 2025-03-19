<?php

namespace FluxErp\Tests\Unit\Action\Order;

use FluxErp\Actions\Order\CreateOrder;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\AddressType;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\User;
use FluxErp\Models\VatRate;
use FluxErp\Tests\TestCase;
use Illuminate\Support\Str;

class CreateOrderTest extends TestCase
{
    private Address $address;

    private Client $client;

    private Currency $currency;

    private Language $language;

    private OrderType $orderType;

    private PaymentType $paymentType;

    private PriceList $priceList;

    private VatRate $vatRate;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a client
        $this->client = Client::factory()->create(['is_default' => true]);

        // Create a contact with address
        $contact = Contact::factory()
            ->has(Address::factory()->for($this->client))
            ->for(PriceList::factory())
            ->for(PaymentType::factory()->hasAttached($this->client))
            ->for($this->client)
            ->create();

        $this->address = $contact->addresses()->first();

        // Create an order type
        $this->orderType = OrderType::factory()->create([
            'client_id' => $this->client->getKey(),
            'order_type_enum' => OrderTypeEnum::Order,
        ]);

        // Create a payment type
        $this->paymentType = PaymentType::factory()
            ->hasAttached($this->client, relationship: 'clients')
            ->create();

        // Create a currency
        $this->currency = Currency::factory()->create(['is_default' => true]);

        // Create a price list
        $this->priceList = PriceList::factory()->create();

        // Create a language
        $this->language = Language::factory()->create(['is_default' => true]);

        // Create a VAT rate
        $this->vatRate = VatRate::factory()->create(['is_tax_exemption' => true]);
    }

    public function test_can_create_order(): void
    {
        $data = [
            'client_id' => $this->client->getKey(),
            'contact_id' => $this->address->contact_id,
            'address_invoice_id' => $this->address->getKey(),
            'currency_id' => $this->currency->getKey(),
            'language_id' => $this->language->getKey(),
            'order_type_id' => $this->orderType->getKey(),
            'payment_type_id' => $this->paymentType->getKey(),
            'price_list_id' => $this->priceList->getKey(),
            'header' => 'Test Header',
            'footer' => 'Test Footer',
            'order_date' => now()->format('Y-m-d'),
            'invoice_number' => Str::random(10),
        ];

        $order = CreateOrder::make($data)
            ->validate()
            ->execute();

        $this->assertNotNull($order);
        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals($data['client_id'], $order->client_id);
        $this->assertEquals($data['contact_id'], $order->contact_id);
        $this->assertEquals($data['address_invoice_id'], $order->address_invoice_id);
        $this->assertEquals($data['order_type_id'], $order->order_type_id);
        $this->assertEquals($data['payment_type_id'], $order->payment_type_id);
        $this->assertEquals($data['header'], $order->header);
        $this->assertEquals($data['footer'], $order->footer);
        $this->assertEquals($data['invoice_number'], $order->invoice_number);
    }

    public function test_can_create_order_with_addresses_and_users(): void
    {
        // Create a second address for testing
        $additionalAddress = Address::factory()->create([
            'client_id' => $this->client->getKey(),
            'contact_id' => $this->address->contact_id,
        ]);

        $addressType = AddressType::factory()->create([
            'client_id' => $this->client->getKey(),
        ]);

        $user = User::factory()
            ->for(Language::factory(), 'language')
            ->create([
                'is_active' => true,
            ]);

        $data = [
            'client_id' => $this->client->getKey(),
            'contact_id' => $this->address->contact_id,
            'address_invoice_id' => $this->address->getKey(),
            'currency_id' => $this->currency->getKey(),
            'order_type_id' => $this->orderType->getKey(),
            'payment_type_id' => $this->paymentType->getKey(),
            'order_date' => now()->format('Y-m-d'),
            'addresses' => [
                [
                    'address_id' => $additionalAddress->getKey(),
                    'address_type_id' => $addressType->getKey(),
                ],
            ],
            'users' => [$user->getKey()], // Use appropriate user IDs
        ];

        $order = CreateOrder::make($data)
            ->validate()
            ->execute();

        $this->assertNotNull($order);

        // Check if addresses were attached
        $this->assertTrue($order->addresses()->where('addresses.id', $additionalAddress->getKey())->exists());

        // Check if users were attached
        // This test will only pass if you have users with ID 1 in your database
        // Adjust accordingly or mock the users relationship
        if (count($data['users']) > 0) {
            $this->assertTrue($order->users()->where('users.id', $user->getKey())->exists());
        }
    }

    public function test_can_create_order_with_shipping_costs(): void
    {
        $shippingCosts = 10.5;

        $data = [
            'client_id' => $this->client->getKey(),
            'contact_id' => $this->address->contact_id,
            'address_invoice_id' => $this->address->getKey(),
            'currency_id' => $this->currency->getKey(),
            'order_type_id' => $this->orderType->getKey(),
            'payment_type_id' => $this->paymentType->getKey(),
            'order_date' => now()->format('Y-m-d'),
            'shipping_costs_net_price' => $shippingCosts,
        ];

        $order = CreateOrder::make($data)
            ->validate()
            ->execute();

        $this->assertNotNull($order);
        $this->assertEquals($shippingCosts, $order->shipping_costs_net_price);
        $this->assertNotNull($order->shipping_costs_gross_price);
        $this->assertNotNull($order->shipping_costs_vat_price);

        // The VAT rate is hardcoded to 19% in the CreateOrder action
        $this->assertEquals(0.19, $order->shipping_costs_vat_rate_percentage);
    }

    public function test_order_gets_default_values(): void
    {
        // Minimal data set
        $data = [
            'client_id' => $this->client->getKey(),
            'address_invoice_id' => $this->address->getKey(),
            'order_type_id' => $this->orderType->getKey(),
            'payment_type_id' => $this->paymentType->getKey(),
            'order_date' => now()->format('Y-m-d'),
        ];

        $order = CreateOrder::make($data)
            ->validate()
            ->execute();

        $this->assertNotNull($order);
        $this->assertInstanceOf(Order::class, $order);

        // Default currency should be applied
        $this->assertEquals($this->currency->getKey(), $order->currency_id);

        // Default address delivery should be set to invoice address if not provided
        $this->assertEquals($order->address_invoice_id, $order->address_delivery_id);
    }
}

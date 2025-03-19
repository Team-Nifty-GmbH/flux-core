<?php

namespace FluxErp\Tests\Unit\Action\Order;

use FluxErp\Actions\Order\UpdateOrder;
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
use FluxErp\Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UpdateOrderTest extends TestCase
{
    private Client $client;

    private Contact $contact;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a client
        $this->client = Client::factory()->create(['is_default' => true]);

        // Create a contact with address
        $this->contact = Contact::factory()
            ->has(Address::factory()->for($this->client))
            ->for(PriceList::factory())
            ->for(PaymentType::factory()->hasAttached($this->client))
            ->for($this->client)
            ->create();

        // Create an order type
        $orderType = OrderType::factory()->create([
            'client_id' => $this->client->getKey(),
            'order_type_enum' => OrderTypeEnum::Order,
        ]);

        // Create a payment type
        $paymentType = PaymentType::factory()
            ->hasAttached($this->client, relationship: 'clients')
            ->create();

        // Create a currency
        $currency = Currency::factory()->create(['is_default' => true]);

        // Create an order
        $this->order = Order::factory()->create([
            'client_id' => $this->client->getKey(),
            'currency_id' => $currency->getKey(),
            'order_type_id' => $orderType->getKey(),
            'payment_type_id' => $paymentType->getKey(),
            'address_invoice_id' => $this->contact->addresses()->first()->getKey(),
            'address_delivery_id' => $this->contact->addresses()->first()->getKey(),
            'header' => 'Original Header',
            'footer' => 'Original Footer',
            'is_locked' => false,
        ]);
    }

    public function test_can_update_address_delivery(): void
    {
        // Create a new delivery address
        $newAddress = Address::factory()->create([
            'client_id' => $this->client->getKey(),
            'contact_id' => $this->order->contact_id,
        ]);

        $data = [
            'id' => $this->order->getKey(),
            'address_delivery_id' => $newAddress->getKey(),
        ];

        $updatedOrder = UpdateOrder::make($data)
            ->validate()
            ->execute();

        $this->assertNotNull($updatedOrder);
        $this->assertEquals($newAddress->getKey(), $updatedOrder->address_delivery_id);
    }

    public function test_can_update_addresses_and_users(): void
    {
        // Create a second address for testing
        $additionalAddress = Address::factory()->create([
            'client_id' => $this->client->getKey(),
            'contact_id' => $this->contact->getKey(),
        ]);
        $addressType = AddressType::factory()->create(['client_id' => $this->client->getKey()]);
        $user = User::factory()
            ->for(Language::factory(), 'language')
            ->create([
                'is_active' => true,
            ]);

        $data = [
            'id' => $this->order->getKey(),
            'addresses' => [
                [
                    'address_id' => $additionalAddress->getKey(),
                    'address_type_id' => $addressType->getKey(),
                ],
            ],
            'users' => [$user->getKey()],
        ];

        $updatedOrder = UpdateOrder::make($data)
            ->validate()
            ->execute();

        $this->assertNotNull($updatedOrder);

        // Check if addresses were attached/updated
        $this->assertTrue($updatedOrder->addresses()->where('addresses.id', $additionalAddress->getKey())->exists());

        // Check if users were attached/updated
        if (count($data['users']) > 0) {
            $this->assertTrue($updatedOrder->users()->where('users.id', $user->getKey())->exists());
        }
    }

    public function test_can_update_order(): void
    {
        $newHeader = 'Updated Header ' . Str::random(5);
        $newFooter = 'Updated Footer ' . Str::random(5);

        $data = [
            'id' => $this->order->getKey(),
            'header' => $newHeader,
            'footer' => $newFooter,
        ];

        $updatedOrder = UpdateOrder::make($data)
            ->validate()
            ->execute();

        $this->assertNotNull($updatedOrder);
        $this->assertEquals($this->order->getKey(), $updatedOrder->getKey());
        $this->assertEquals($newHeader, $updatedOrder->header);
        $this->assertEquals($newFooter, $updatedOrder->footer);

        // Verify database was updated
        $this->assertDatabaseHas('orders', [
            'id' => $this->order->getKey(),
            'header' => $newHeader,
            'footer' => $newFooter,
        ]);
    }

    public function test_can_update_shipping_costs(): void
    {
        $initialShippingCost = $this->order->shipping_costs_net_price ?? 0;
        $newShippingCost = $initialShippingCost + 15.75;

        $data = [
            'id' => $this->order->getKey(),
            'shipping_costs_net_price' => $newShippingCost,
        ];

        $updatedOrder = UpdateOrder::make($data)
            ->validate()
            ->execute();

        $this->assertNotNull($updatedOrder);
        $this->assertEquals($newShippingCost, $updatedOrder->shipping_costs_net_price);
        $this->assertNotNull($updatedOrder->shipping_costs_gross_price);
        $this->assertNotNull($updatedOrder->shipping_costs_vat_price);

        // The VAT rate is hardcoded to 19% in the UpdateOrder action
        $this->assertEquals(0.19, $updatedOrder->shipping_costs_vat_rate_percentage);
    }

    public function test_cannot_update_invoice_number_to_existing_value(): void
    {
        // Create a second order with a unique invoice number
        $existingInvoiceNumber = 'INV-' . Str::random(8);

        Order::factory()->create([
            'address_invoice_id' => $this->contact->addresses()->first()->getKey(),
            'client_id' => $this->client->getKey(),
            'currency_id' => $this->order->currency_id,
            'order_type_id' => $this->order->order_type_id,
            'invoice_number' => $existingInvoiceNumber,
        ]);

        $data = [
            'id' => $this->order->getKey(),
            'invoice_number' => $existingInvoiceNumber, // Try to use existing invoice number
        ];

        // Should throw validation exception
        $this->expectException(ValidationException::class);

        try {
            UpdateOrder::make($data)
                ->validate()
                ->execute();
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('invoice_number', $e->errors());
            throw $e;
        }

        // Order should not be updated
        $this->assertDatabaseMissing('orders', [
            'id' => $this->order->getKey(),
            'invoice_number' => $existingInvoiceNumber,
        ]);
    }

    public function test_cannot_update_locked_order(): void
    {
        // Lock the order
        $this->order->is_locked = true;
        $this->order->save();

        $data = [
            'id' => $this->order->getKey(),
            'header' => 'New Header for Locked Order',
        ];

        // Should throw validation exception
        $this->expectException(ValidationException::class);

        try {
            UpdateOrder::make($data)
                ->validate()
                ->execute();
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('is_locked', $e->errors());
            throw $e;
        }

        // Order should not be updated
        $this->assertDatabaseMissing('orders', [
            'id' => $this->order->getKey(),
            'header' => $data['header'],
        ]);
    }
}

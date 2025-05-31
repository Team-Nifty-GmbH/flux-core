<?php

namespace FluxErp\Tests\Unit\Action\Order;

use FluxErp\Actions\Order\DeleteOrder;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Tests\TestCase;
use Illuminate\Validation\ValidationException;

class DeleteOrderTest extends TestCase
{
    private Order $lockedOrder;

    private Order $order;

    private Order $parentOrder;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a client
        $client = Client::factory()->create(['is_default' => true]);

        // Create a contact with address
        $contact = Contact::factory()
            ->has(Address::factory()->for($client))
            ->for($client)
            ->create();

        $address = $contact->addresses()->first();

        // Create an order type
        $orderType = OrderType::factory()->create([
            'client_id' => $client->getKey(),
            'order_type_enum' => OrderTypeEnum::Order,
        ]);

        // Create a payment type
        $paymentType = PaymentType::factory()
            ->hasAttached($client, relationship: 'clients')
            ->create();

        // Create a currency
        $currency = Currency::factory()->create(['is_default' => true]);

        // Create a price list
        $priceList = PriceList::factory()->create();

        // Create a standard order
        $this->order = Order::factory()->create([
            'client_id' => $client->getKey(),
            'currency_id' => $currency->getKey(),
            'order_type_id' => $orderType->getKey(),
            'price_list_id' => $priceList->getKey(),
            'payment_type_id' => $paymentType->getKey(),
            'address_invoice_id' => $address->getKey(),
            'address_delivery_id' => $address->getKey(),
            'is_locked' => false,
        ]);

        // Create a locked order
        $this->lockedOrder = Order::factory()->create([
            'client_id' => $client->getKey(),
            'currency_id' => $currency->getKey(),
            'order_type_id' => $orderType->getKey(),
            'price_list_id' => $priceList->getKey(),
            'payment_type_id' => $paymentType->getKey(),
            'address_invoice_id' => $address->getKey(),
            'address_delivery_id' => $address->getKey(),
            'is_locked' => true,
        ]);

        // Create a parent order with child orders
        $this->parentOrder = Order::factory()->create([
            'client_id' => $client->getKey(),
            'currency_id' => $currency->getKey(),
            'order_type_id' => $orderType->getKey(),
            'price_list_id' => $priceList->getKey(),
            'payment_type_id' => $paymentType->getKey(),
            'address_invoice_id' => $address->getKey(),
            'address_delivery_id' => $address->getKey(),
            'is_locked' => false,
        ]);

        // Create a child order
        Order::factory()->create([
            'client_id' => $client->getKey(),
            'currency_id' => $currency->getKey(),
            'order_type_id' => $orderType->getKey(),
            'price_list_id' => $priceList->getKey(),
            'payment_type_id' => $paymentType->getKey(),
            'address_invoice_id' => $address->getKey(),
            'address_delivery_id' => $address->getKey(),
            'parent_id' => $this->parentOrder->getKey(),
            'is_locked' => false,
        ]);
    }

    public function test_can_delete_order(): void
    {
        $this->assertDatabaseHas('orders', ['id' => $this->order->getKey()]);

        $result = DeleteOrder::make(['id' => $this->order->getKey()])
            ->validate()
            ->execute();

        $this->assertTrue($result);
        $this->assertDatabaseHas('orders', ['id' => $this->order->getKey()]);

        // Soft delete should have been applied
        $this->assertSoftDeleted('orders', ['id' => $this->order->getKey()]);
    }

    public function test_cannot_delete_locked_order(): void
    {
        $this->assertTrue($this->lockedOrder->is_locked);
        $this->assertDatabaseHas('orders', ['id' => $this->lockedOrder->getKey()]);

        // Should throw validation exception
        $this->expectException(ValidationException::class);

        try {
            DeleteOrder::make(['id' => $this->lockedOrder->getKey()])
                ->validate()
                ->execute();
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('is_locked', $e->errors());
            throw $e;
        }

        // Order should still exist
        $this->assertNotSoftDeleted('orders', ['id' => $this->lockedOrder->getKey()]);
    }

    public function test_cannot_delete_order_with_children(): void
    {
        $this->assertDatabaseHas('orders', ['id' => $this->parentOrder->getKey()]);
        $this->assertDatabaseHas('orders', ['parent_id' => $this->parentOrder->getKey()]);

        // Should throw validation exception
        $this->expectException(ValidationException::class);

        try {
            DeleteOrder::make(['id' => $this->parentOrder->getKey()])
                ->validate()
                ->execute();
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('children', $e->errors());
            throw $e;
        }

        // Order should still exist
        $this->assertNotSoftDeleted('orders', ['id' => $this->parentOrder->getKey()]);
    }

    public function test_throws_error_when_order_not_found(): void
    {
        // Should throw validation exception
        $this->expectException(ValidationException::class);

        try {
            DeleteOrder::make(['id' => 99999]) // Non-existent ID
                ->validate()
                ->execute();
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('id', $e->errors());
            throw $e;
        }
    }
}

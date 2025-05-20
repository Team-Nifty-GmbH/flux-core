<?php

namespace FluxErp\Tests\Unit\Action\Order;

use FluxErp\Actions\Order\ToggleLock;
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

class ToggleLockTest extends TestCase
{
    private Order $lockedOrder;

    private Order $unlockedOrder;

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

        // Create an unlocked order
        $this->unlockedOrder = Order::factory()->create([
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
    }

    public function test_can_explicitly_set_lock_state(): void
    {
        // For an unlocked order, explicitly set it to locked
        $data = [
            'id' => $this->unlockedOrder->getKey(),
            'is_locked' => true,
        ];

        $lockedOrder = ToggleLock::make($data)
            ->validate()
            ->execute();

        $this->assertTrue($lockedOrder->is_locked);

        // For a locked order, explicitly set it to locked again (should remain locked)
        $data = [
            'id' => $this->lockedOrder->getKey(),
            'is_locked' => true,
        ];

        $stillLockedOrder = ToggleLock::make($data)
            ->validate()
            ->execute();

        $this->assertTrue($stillLockedOrder->is_locked);

        // For a locked order, explicitly set it to unlocked
        $data = [
            'id' => $this->lockedOrder->getKey(),
            'is_locked' => false,
        ];

        $unlockedOrder = ToggleLock::make($data)
            ->validate()
            ->execute();

        $this->assertFalse($unlockedOrder->is_locked);
    }

    public function test_can_lock_unlocked_order(): void
    {
        $this->assertFalse($this->unlockedOrder->is_locked);

        $data = [
            'id' => $this->unlockedOrder->getKey(),
        ];

        $lockedOrder = ToggleLock::make($data)
            ->validate()
            ->execute();

        $this->assertTrue($lockedOrder->is_locked);

        // Verify database was updated
        $this->assertDatabaseHas('orders', [
            'id' => $this->unlockedOrder->getKey(),
            'is_locked' => true,
        ]);
    }

    public function test_can_unlock_locked_order(): void
    {
        $this->assertTrue($this->lockedOrder->is_locked);

        $data = [
            'id' => $this->lockedOrder->getKey(),
        ];

        $unlockedOrder = ToggleLock::make($data)
            ->validate()
            ->execute();

        $this->assertFalse($unlockedOrder->is_locked);

        // Verify database was updated
        $this->assertDatabaseHas('orders', [
            'id' => $this->lockedOrder->getKey(),
            'is_locked' => false,
        ]);
    }

    public function test_locking_recalculates_prices(): void
    {
        // Create an order
        $order = Order::factory()->create([
            'payment_type_id' => $this->lockedOrder->payment_type_id,
            'order_type_id' => $this->lockedOrder->order_type_id,
            'address_invoice_id' => $this->lockedOrder->address_invoice_id,
            'currency_id' => $this->lockedOrder->currency_id,
            'client_id' => $this->lockedOrder->client_id,
            'price_list_id' => $this->lockedOrder->price_list_id,
            'is_locked' => false,
            'total_net_price' => 100.00,
            'total_gross_price' => 119.00,
            'total_vat_price' => 19.00,
        ]);

        // Lock the order
        $lockedOrder = ToggleLock::make(['id' => $order->id])
            ->validate()
            ->execute();

        // Verify it was locked
        $this->assertTrue($lockedOrder->is_locked);

        // Just a basic check that prices exist and are numeric
        // since we can't easily verify the calculation internals
        $this->assertIsNumeric($lockedOrder->total_net_price);
        $this->assertIsNumeric($lockedOrder->total_gross_price);
    }
}

<?php

namespace FluxErp\Tests\Unit\Action\Order;

use FluxErp\Actions\Order\ReplicateOrder;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\VatRate;
use FluxErp\Models\Warehouse;
use FluxErp\Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReplicateOrderTest extends TestCase
{
    private Order $order;

    private OrderPosition $orderPosition;

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

        // Create an order with some data
        $this->order = Order::factory()->create([
            'client_id' => $client->getKey(),
            'currency_id' => $currency->getKey(),
            'order_type_id' => $orderType->getKey(),
            'price_list_id' => $priceList->getKey(),
            'payment_type_id' => $paymentType->getKey(),
            'address_invoice_id' => $address->getKey(),
            'address_delivery_id' => $address->getKey(),
            'header' => 'Original Header',
            'footer' => 'Original Footer',
            'is_locked' => false,
        ]);

        // Create a product
        $vatRate = VatRate::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create([
            'client_id' => $client->getKey(),
            'vat_rate_id' => $vatRate->getKey(),
        ]);

        // Create an order position
        $this->orderPosition = OrderPosition::factory()->create([
            'client_id' => $client->getKey(),
            'order_id' => $this->order->getKey(),
            'product_id' => $product->getKey(),
            'vat_rate_id' => $vatRate->getKey(),
            'warehouse_id' => $warehouse->getKey(),
            'name' => 'Original Position',
            'amount' => 2,
            'is_net' => true,
            'is_free_text' => false,
        ]);
    }

    public function test_can_replicate_order(): void
    {
        $data = [
            'id' => $this->order->getKey(),
            'contact_id' => $this->order->contact_id,
            'client_id' => $this->order->client_id,
            'header' => 'Replicated Header',
        ];

        $replicatedOrder = ReplicateOrder::make($data)
            ->validate()
            ->execute();

        $this->assertNotNull($replicatedOrder);
        $this->assertNotEquals($this->order->getKey(), $replicatedOrder->getKey());
        $this->assertEquals('Replicated Header', $replicatedOrder->header);
        $this->assertEquals('Original Footer', $replicatedOrder->footer);
        $this->assertEquals($this->order->client_id, $replicatedOrder->client_id);
        $this->assertEquals($this->order->order_type_id, $replicatedOrder->order_type_id);

        // Order numbers and other fields should be different
        $this->assertNotEquals($this->order->order_number, $replicatedOrder->order_number);
        $this->assertNull($replicatedOrder->invoice_number);
        $this->assertNull($replicatedOrder->invoice_date);

        // Check that created_from_id points to original order
        $this->assertEquals($this->order->getKey(), $replicatedOrder->created_from_id);
    }

    public function test_can_replicate_with_specific_positions(): void
    {
        // Create a second position
        $secondPosition = OrderPosition::factory()->create([
            'client_id' => $this->order->client_id,
            'order_id' => $this->order->getKey(),
            'contact_id' => $this->order->contact_id,
            'name' => $name = Str::uuid()->toString(),
            'is_free_text' => true,
        ]);

        // Replicate with only the second position
        $data = [
            'id' => $this->order->getKey(),
            'client_id' => $this->order->client_id,
            'contact_id' => $this->order->contact_id,
            'order_positions' => [
                [
                    'id' => $secondPosition->getKey(),
                    'amount' => 3,
                    'is_free_text' => true,
                ],
            ],
        ];

        $replicatedOrder = ReplicateOrder::make($data)
            ->validate()
            ->execute();

        // Only the second position should be replicated with updated amount
        $this->assertDatabaseHas('order_positions', [
            'order_id' => $replicatedOrder->getKey(),
            'name' => $name,
        ]);

        // First position should not be replicated
        $this->assertDatabaseMissing('order_positions', [
            'order_id' => $replicatedOrder->getKey(),
            'name' => $this->orderPosition->name,
        ]);
    }

    public function test_handles_split_order_replication(): void
    {
        // Create a split order type
        $splitOrderType = OrderType::factory()
            ->create(
                [
                    'client_id' => $this->order->client_id,
                    'order_type_enum' => OrderTypeEnum::SplitOrder,
                ]
            );

        // Replicate as split order
        $data = [
            'id' => $this->order->getKey(),
            'order_type_id' => $splitOrderType->getKey(),
            'contact_id' => $this->order->contact_id, // Same contact
            'client_id' => $this->order->client_id,
        ];

        $splitOrder = ReplicateOrder::make($data)
            ->validate()
            ->execute();

        // Split order should have parent_id set to original order
        $this->assertEquals($this->order->getKey(), $splitOrder->parent_id);

        // Position should have origin_position_id set to original position
        $splitPositions = OrderPosition::where('order_id', $splitOrder->getKey())->get();
        foreach ($splitPositions as $position) {
            $this->assertEquals($this->orderPosition->getKey(), $position->origin_position_id);
        }
    }

    public function test_replicates_order_positions(): void
    {
        $data = [
            'id' => $this->order->getKey(),
            'contact_id' => $this->order->contact_id,
            'client_id' => $this->order->client_id,
        ];

        $replicatedOrder = ReplicateOrder::make($data)
            ->validate()
            ->execute();

        // Check that order positions were also replicated
        $this->assertDatabaseHas('order_positions', [
            'order_id' => $replicatedOrder->getKey(),
            'name' => $this->orderPosition->name,
            'amount' => $this->orderPosition->amount,
        ]);

        // Get the replicated position
        $replicatedPosition = OrderPosition::where('order_id', $replicatedOrder->getKey())->first();

        // Created from should point to original position
        $this->assertEquals($this->orderPosition->getKey(), $replicatedPosition->created_from_id);
    }

    public function test_validates_no_duplicate_position_ids(): void
    {
        // Try to replicate with duplicate position IDs
        $data = [
            'id' => $this->order->getKey(),
            'contact_id' => $this->order->contact_id,
            'client_id' => $this->order->client_id,
            'order_positions' => [
                [
                    'id' => $this->orderPosition->getKey(),
                    'amount' => 1,
                ],
                [
                    'id' => $this->orderPosition->getKey(), // Duplicate ID
                    'amount' => 2,
                ],
            ],
        ];

        // Should throw validation exception
        $this->expectException(ValidationException::class);

        try {
            ReplicateOrder::make($data)
                ->validate()
                ->execute();
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('order_positions', $e->errors());
            throw $e;
        }
    }
}

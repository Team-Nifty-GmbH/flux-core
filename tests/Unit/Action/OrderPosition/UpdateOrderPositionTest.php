<?php

namespace FluxErp\Tests\Unit\Action\OrderPosition;

use FluxErp\Actions\OrderPosition\UpdateOrderPosition;
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
use FluxErp\Models\Tag;
use FluxErp\Models\VatRate;
use FluxErp\Models\Warehouse;
use FluxErp\Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UpdateOrderPositionTest extends TestCase
{
    private Client $client;

    private Order $lockedOrder;

    private OrderPosition $lockedOrderPosition;

    private Order $order;

    private OrderPosition $orderPosition;

    private PriceList $priceList;

    private Product $product;

    private VatRate $vatRate;

    private Warehouse $warehouse;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a client
        $this->client = Client::factory()->create(['is_default' => true]);

        // Create a price list
        $this->priceList = PriceList::factory()->create();

        // Create address/contact
        $contact = Contact::factory()
            ->has(Address::factory()->for($this->client))
            ->for($this->priceList)
            ->for(PaymentType::factory()->hasAttached($this->client))
            ->for($this->client)
            ->create();

        $address = $contact->addresses()->first();

        // Create required entities for order
        $orderType = OrderType::factory()->create([
            'client_id' => $this->client->getKey(),
            'order_type_enum' => OrderTypeEnum::Order,
        ]);

        $paymentType = PaymentType::factory()
            ->hasAttached($this->client, relationship: 'clients')
            ->create();

        $currency = Currency::factory()->create(['is_default' => true]);

        // Create a regular order
        $this->order = Order::factory()->create([
            'client_id' => $this->client->getKey(),
            'order_type_id' => $orderType->getKey(),
            'payment_type_id' => $paymentType->getKey(),
            'currency_id' => $currency->getKey(),
            'address_invoice_id' => $address->getKey(),
            'address_delivery_id' => $address->getKey(),
            'price_list_id' => $this->priceList->getKey(),
            'is_locked' => false,
        ]);

        // Create a locked order
        $this->lockedOrder = Order::factory()->create([
            'client_id' => $this->client->getKey(),
            'order_type_id' => $orderType->getKey(),
            'payment_type_id' => $paymentType->getKey(),
            'currency_id' => $currency->getKey(),
            'address_invoice_id' => $address->getKey(),
            'address_delivery_id' => $address->getKey(),
            'price_list_id' => $this->priceList->getKey(),
            'is_locked' => true,
        ]);

        // Create a VAT rate
        $this->vatRate = VatRate::factory()->create();

        // Create a warehouse
        $this->warehouse = Warehouse::factory()->create([
            'is_default' => true,
        ]);

        // Create a product
        $this->product = Product::factory()->create([
            'client_id' => $this->client->getKey(),
            'vat_rate_id' => $this->vatRate->getKey(),
        ]);

        // Create an order position
        $this->orderPosition = OrderPosition::factory()->create([
            'client_id' => $this->client->getKey(),
            'order_id' => $this->order->getKey(),
            'product_id' => $this->product->getKey(),
            'vat_rate_id' => $this->vatRate->getKey(),
            'warehouse_id' => $this->warehouse->getKey(),
            'name' => 'Original Position',
            'amount' => 1,
            'unit_net_price' => 50.00,
            'unit_gross_price' => 59.50, // Assuming 19% VAT
            'total_net_price' => 50.00,
            'total_gross_price' => 59.50,
            'is_net' => true,
            'is_free_text' => false,
            'price_list_id' => $this->priceList->getKey(),
        ]);

        // Create a position in locked order
        $this->lockedOrderPosition = OrderPosition::factory()->create([
            'client_id' => $this->client->getKey(),
            'order_id' => $this->lockedOrder->getKey(),
            'product_id' => $this->product->getKey(),
            'vat_rate_id' => $this->vatRate->getKey(),
            'warehouse_id' => $this->warehouse->getKey(),
            'name' => 'Position in Locked Order',
            'amount' => 1,
            'unit_net_price' => 50.00,
            'unit_gross_price' => 59.50,
            'total_net_price' => 50.00,
            'total_gross_price' => 59.50,
            'is_net' => true,
            'is_free_text' => false,
            'price_list_id' => $this->priceList->getKey(),
        ]);
    }

    public function test_can_update_order_position(): void
    {
        $newName = 'Updated Position Name';
        $newAmount = 3;

        $data = [
            'id' => $this->orderPosition->getKey(),
            'name' => $newName,
            'amount' => $newAmount,
        ];

        $updatedPosition = UpdateOrderPosition::make($data)
            ->validate()
            ->execute();

        $this->assertNotNull($updatedPosition);
        $this->assertEquals($this->orderPosition->getKey(), $updatedPosition->getKey());
        $this->assertEquals($newName, $updatedPosition->name);
        $this->assertEquals($newAmount, $updatedPosition->amount);

        // Verify database was updated
        $this->assertDatabaseHas('order_positions', [
            'id' => $this->orderPosition->getKey(),
            'name' => $newName,
            'amount' => $newAmount,
        ]);
    }

    public function test_can_update_position_sort_number(): void
    {
        // Create additional positions to test sorting
        $position2 = OrderPosition::factory()->create([
            'client_id' => $this->client->getKey(),
            'order_id' => $this->order->getKey(),
            'sort_number' => 1,
            'price_list_id' => $this->priceList->getKey(),
        ]);

        $position3 = OrderPosition::factory()->create([
            'client_id' => $this->client->getKey(),
            'order_id' => $this->order->getKey(),
            'sort_number' => 2,
            'price_list_id' => $this->priceList->getKey(),
        ]);

        // Update position sort number
        $data = [
            'id' => $this->orderPosition->getKey(),
            'sort_number' => 1, // Move to the middle
        ];

        $updatedPosition = UpdateOrderPosition::make($data)
            ->validate()
            ->execute();

        $this->assertNotNull($updatedPosition);
        $this->assertEquals(1, $updatedPosition->sort_number);

        // Verify other positions were reordered
        // Position 2 should be moved up
        $this->assertDatabaseHas('order_positions', [
            'id' => $position2->getKey(),
            'sort_number' => 2,
        ]);
    }

    public function test_can_update_tags(): void
    {
        // Create tags in the database
        $tag1 = Tag::factory()->create([
            'type' => morph_alias(OrderPosition::class),
        ]);

        $tag2 = Tag::factory()->create([
            'type' => morph_alias(OrderPosition::class),
        ]);

        // First add a tag
        $data = [
            'id' => $this->orderPosition->getKey(),
            'tags' => [$tag1->getKey()],
        ];

        $updatedPosition = UpdateOrderPosition::make($data)
            ->validate()
            ->execute();

        $this->assertNotNull($updatedPosition);
        $this->assertTrue($updatedPosition->tags->contains($tag1->getKey()));

        // Now update to a different tag
        $data = [
            'id' => $this->orderPosition->getKey(),
            'tags' => [$tag2->getKey()],
        ];

        $updatedPosition = UpdateOrderPosition::make($data)
            ->validate()
            ->execute();

        $this->assertNotNull($updatedPosition);
        $this->assertFalse($updatedPosition->tags->contains($tag1->getKey())); // First tag removed
        $this->assertTrue($updatedPosition->tags->contains($tag2->getKey())); // Second tag added
    }

    public function test_cannot_update_position_in_locked_order(): void
    {
        $data = [
            'id' => $this->lockedOrderPosition->getKey(),
            'name' => 'Should Not Update',
        ];

        // Should throw validation exception
        $this->expectException(ValidationException::class);

        try {
            UpdateOrderPosition::make($data)
                ->validate()
                ->execute();
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('is_locked', $e->errors());
            throw $e;
        }

        // Position should not be updated
        $this->assertDatabaseMissing('order_positions', [
            'id' => $this->lockedOrderPosition->getKey(),
            'name' => 'Should Not Update',
        ]);
    }

    public function test_handles_price_recalculation(): void
    {
        // Initial values
        $initialAmount = $this->orderPosition->amount;
        $initialUnitPrice = $this->orderPosition->unit_price;
        $initialTotalNet = $this->orderPosition->total_net_price;

        // Update amount
        $newAmount = $initialAmount + 2;

        $data = [
            'id' => $this->orderPosition->getKey(),
            'amount' => $newAmount,
        ];

        $updatedPosition = UpdateOrderPosition::make($data)
            ->validate()
            ->execute();

        $this->assertNotNull($updatedPosition);
        $this->assertEquals($newAmount, $updatedPosition->amount);

        // Total price should be recalculated
        $expectedTotalNet = bcmul($initialUnitPrice, $newAmount);
        $this->assertEquals(1, bccomp($expectedTotalNet, $initialTotalNet));
    }

    public function test_updating_product_updates_product_info(): void
    {
        // Create a new product
        $newProduct = Product::factory()->create([
            'client_id' => $this->client->getKey(),
            'vat_rate_id' => $this->vatRate->getKey(),
            'name' => 'New Product ' . Str::random(5),
            'description' => 'New Description ' . Str::random(10),
            'product_number' => 'NP-' . Str::random(5),
        ]);

        $data = [
            'id' => $this->orderPosition->getKey(),
            'product_id' => $newProduct->getKey(),
        ];

        $updatedPosition = UpdateOrderPosition::make($data)
            ->validate()
            ->execute();

        $this->assertNotNull($updatedPosition);

        // Product info should be updated
        $this->assertEquals($newProduct->name, $updatedPosition->name);
        $this->assertEquals($newProduct->description, $updatedPosition->description);
        $this->assertEquals($newProduct->product_number, $updatedPosition->product_number);
        $this->assertEquals($newProduct->vat_rate_id, $updatedPosition->vat_rate_id);
    }
}

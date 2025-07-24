<?php

namespace FluxErp\Tests\Unit\Action\OrderPosition;

use FluxErp\Actions\OrderPosition\CreateOrderPosition;
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
use Illuminate\Validation\ValidationException;

class CreateOrderPositionTest extends TestCase
{
    private Client $client;

    private Order $lockedOrder;

    private Order $order;

    private Product $product;

    private VatRate $vatRate;

    private Warehouse $warehouse;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a client
        $this->client = Client::factory()->create(['is_default' => true]);

        // Create address/contact
        $contact = Contact::factory()
            ->has(Address::factory()->for($this->client))
            ->for(PriceList::factory())
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
    }

    public function test_can_create_free_text_order_position(): void
    {
        $data = [
            'client_id' => $this->client->getKey(),
            'order_id' => $this->order->getKey(),
            'name' => 'Free Text Item',
            'description' => 'This is a free text item description',
            'is_free_text' => true,
        ];

        $orderPosition = CreateOrderPosition::make($data)
            ->validate()
            ->execute();

        $this->assertNotNull($orderPosition);
        $this->assertEquals($data['name'], $orderPosition->name);
        $this->assertEquals($data['description'], $orderPosition->description);
        $this->assertTrue($orderPosition->is_free_text);

        // Free text items should not have prices
        $this->assertNull($orderPosition->unit_net_price);
        $this->assertNull($orderPosition->unit_gross_price);
        $this->assertNull($orderPosition->total_net_price);
        $this->assertNull($orderPosition->total_gross_price);
    }

    public function test_can_create_order_position(): void
    {
        $data = [
            'client_id' => $this->client->getKey(),
            'order_id' => $this->order->getKey(),
            'product_id' => $this->product->getKey(),
            'vat_rate_id' => $this->vatRate->getKey(),
            'warehouse_id' => $this->warehouse->getKey(),
            'name' => 'Test Product',
            'amount' => 2,
            'unit_price' => 50.00,
            'is_net' => true,
            'is_free_text' => false,
        ];

        $orderPosition = CreateOrderPosition::make($data)
            ->validate()
            ->execute();

        $this->assertNotNull($orderPosition);
        $this->assertInstanceOf(OrderPosition::class, $orderPosition);
        $this->assertEquals($data['client_id'], $orderPosition->client_id);
        $this->assertEquals($data['order_id'], $orderPosition->order_id);
        $this->assertEquals($data['name'], $orderPosition->name);
        $this->assertEquals($data['amount'], $orderPosition->amount);
        $this->assertEquals($data['unit_price'], $orderPosition->unit_price);

        // Check price calculations
        $this->assertNotNull($orderPosition->unit_net_price);
        $this->assertNotNull($orderPosition->unit_gross_price);
        $this->assertNotNull($orderPosition->total_net_price);
        $this->assertNotNull($orderPosition->total_gross_price);
        $this->assertNotNull($orderPosition->vat_price);
    }

    public function test_can_create_position_with_tags(): void
    {
        // Create tags in the database
        $tag1 = Tag::factory()->create([
            'type' => morph_alias(OrderPosition::class),
        ]);

        $tag2 = Tag::factory()->create([
            'type' => morph_alias(OrderPosition::class),
        ]);

        $data = [
            'client_id' => $this->client->getKey(),
            'order_id' => $this->order->getKey(),
            'product_id' => $this->product->getKey(),
            'vat_rate_id' => $this->vatRate->getKey(),
            'warehouse_id' => $this->warehouse->getKey(),
            'name' => 'Position with Tags',
            'amount' => 1,
            'unit_price' => 50.00,
            'is_net' => true,
            'is_free_text' => false,
            'tags' => [$tag1->getKey(), $tag2->getKey()],
        ];

        $orderPosition = CreateOrderPosition::make($data)
            ->validate()
            ->execute();

        $this->assertNotNull($orderPosition);

        // Check that tags were attached
        $this->assertTrue($orderPosition->tags->contains($tag1->getKey()));
        $this->assertTrue($orderPosition->tags->contains($tag2->getKey()));
    }

    public function test_cannot_create_position_for_locked_order(): void
    {
        $data = [
            'client_id' => $this->client->getKey(),
            'order_id' => $this->lockedOrder->getKey(),
            'product_id' => $this->product->getKey(),
            'vat_rate_id' => $this->vatRate->getKey(),
            'warehouse_id' => $this->warehouse->getKey(),
            'name' => 'Position for Locked Order',
            'amount' => 1,
            'unit_price' => 25.00,
            'is_net' => true,
            'is_free_text' => false,
        ];

        // Should throw validation exception
        $this->expectException(ValidationException::class);

        try {
            CreateOrderPosition::make($data)
                ->validate()
                ->execute();
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('is_locked', $e->errors());
            throw $e;
        }
    }

    public function test_creates_bundle_positions_for_bundle_product(): void
    {
        // Create a bundle product with sub-products
        $bundleProduct = Product::factory()->create([
            'client_id' => $this->client->getKey(),
            'vat_rate_id' => $this->vatRate->getKey(),
            'is_bundle' => true,
        ]);

        // Create bundle sub-products
        $subProduct1 = Product::factory()->create([
            'client_id' => $this->client->getKey(),
            'vat_rate_id' => $this->vatRate->getKey(),
        ]);

        $subProduct2 = Product::factory()->create([
            'client_id' => $this->client->getKey(),
            'vat_rate_id' => $this->vatRate->getKey(),
        ]);

        // Attach sub-products to bundle
        $bundleProduct->bundleProducts()->attach([
            $subProduct1->getKey() => ['count' => 2],
            $subProduct2->getKey() => ['count' => 1],
        ]);

        $data = [
            'client_id' => $this->client->getKey(),
            'order_id' => $this->order->getKey(),
            'product_id' => $bundleProduct->getKey(),
            'vat_rate_id' => $this->vatRate->getKey(),
            'warehouse_id' => $this->warehouse->getKey(),
            'name' => $bundleProduct->name,
            'amount' => 3,
            'unit_price' => 100.00,
            'is_net' => true,
            'is_free_text' => false,
        ];

        $bundlePosition = CreateOrderPosition::make($data)
            ->validate()
            ->execute();

        $this->assertNotNull($bundlePosition);

        // Check that bundle sub-positions were created
        $subPositions = OrderPosition::where('parent_id', $bundlePosition->getKey())->get();

        $this->assertCount(2, $subPositions);

        // Check that the amount is correctly calculated
        // First sub-product: 2 per bundle * 3 bundles = 6
        $subPosition1 = $subPositions->where('product_id', $subProduct1->getKey())->first();
        $this->assertEquals(6, $subPosition1->amount);

        // Second sub-product: 1 per bundle * 3 bundles = 3
        $subPosition2 = $subPositions->where('product_id', $subProduct2->getKey())->first();
        $this->assertEquals(3, $subPosition2->amount);

        // Check that bundle positions are marked correctly
        foreach ($subPositions as $position) {
            $this->assertTrue($position->is_bundle_position);
            $this->assertFalse($position->is_free_text);
            $this->assertEquals($bundlePosition->getKey(), $position->parent_id);
        }
    }

    public function test_defaults_to_one_item_for_non_free_text_positions(): void
    {
        $data = [
            'client_id' => $this->client->getKey(),
            'order_id' => $this->order->getKey(),
            'product_id' => $this->product->getKey(),
            'vat_rate_id' => $this->vatRate->getKey(),
            'warehouse_id' => $this->warehouse->getKey(),
            'name' => 'Position with Default Amount',
            // amount not specified
            'unit_price' => 50.00,
            'is_net' => true,
            'is_free_text' => false,
        ];

        $orderPosition = CreateOrderPosition::make($data)
            ->validate()
            ->execute();

        $this->assertNotNull($orderPosition);
        $this->assertEquals(1, $orderPosition->amount); // Default amount is 1
    }

    public function test_position_gets_product_info_when_provided(): void
    {
        $data = [
            'client_id' => $this->client->getKey(),
            'order_id' => $this->order->getKey(),
            'product_id' => $this->product->getKey(),
            'warehouse_id' => $this->warehouse->getKey(),
            'is_net' => true,
            'is_free_text' => false,
        ];

        $orderPosition = CreateOrderPosition::make($data)
            ->validate()
            ->execute();

        $this->assertNotNull($orderPosition);

        // Should have product information
        $this->assertEquals($this->product->name, $orderPosition->name);
        $this->assertEquals($this->product->description, $orderPosition->description);
        $this->assertEquals($this->product->product_number, $orderPosition->product_number);
        $this->assertEquals($this->product->vat_rate_id, $orderPosition->vat_rate_id);
    }

    public function test_validates_required_fields(): void
    {
        // Missing required fields
        $data = [
            'client_id' => $this->client->getKey(),
            'order_id' => $this->order->getKey(),
            // Missing name, and no product_id either
            'is_free_text' => false,
        ];

        // Should throw validation exception
        $this->expectException(ValidationException::class);

        try {
            CreateOrderPosition::make($data)
                ->validate()
                ->execute();
        } catch (ValidationException $e) {
            // Should complain about missing name or product_id
            $this->assertArrayHasKey('name', $e->errors());
            throw $e;
        }
    }
}

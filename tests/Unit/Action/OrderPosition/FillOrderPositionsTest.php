<?php

namespace FluxErp\Tests\Unit\Action\OrderPosition;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\OrderPosition\FillOrderPositions;
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
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;

class FillOrderPositionsTest extends TestCase
{
    private Product $bundleProduct;

    private Client $client;

    private OrderPosition $existingPosition;

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

        // Create a VAT rate
        $this->vatRate = VatRate::factory()->create();

        // Create a warehouse
        $this->warehouse = Warehouse::factory()->create([
            'is_default' => true,
        ]);

        // Create a regular product
        $this->product = Product::factory()->create([
            'client_id' => $this->client->getKey(),
            'vat_rate_id' => $this->vatRate->getKey(),
        ]);

        // Create a bundle product
        $this->bundleProduct = Product::factory()->create([
            'client_id' => $this->client->getKey(),
            'vat_rate_id' => $this->vatRate->getKey(),
            'name' => 'Bundle Product',
            'is_bundle' => true,
        ]);

        // Add products to the bundle
        $subProduct = Product::factory()->create([
            'client_id' => $this->client->getKey(),
            'vat_rate_id' => $this->vatRate->getKey(),
            'name' => 'Bundle Component',
        ]);

        $this->bundleProduct->bundleProducts()->attach([
            $subProduct->getKey() => ['count' => 2],
        ]);

        // Create an existing order position
        $this->existingPosition = OrderPosition::factory()->create([
            'client_id' => $this->client->getKey(),
            'order_id' => $this->order->getKey(),
            'product_id' => $this->product->getKey(),
            'vat_rate_id' => $this->vatRate->getKey(),
            'warehouse_id' => $this->warehouse->getKey(),
            'name' => 'Existing Position',
            'amount' => 1,
            'is_net' => true,
            'is_free_text' => false,
        ]);
    }

    public function test_bundle_position_handling(): void
    {
        // Test data with a bundle position
        $data = [
            'order_id' => $this->order->getKey(),
            'simulate' => false,
            'order_positions' => [
                [
                    'order_id' => $this->order->getKey(),
                    'client_id' => $this->client->getKey(),
                    'product_id' => $this->bundleProduct->getKey(),
                    'vat_rate_id' => $this->vatRate->getKey(),
                    'warehouse_id' => $this->warehouse->getKey(),
                    'name' => 'Bundle Position',
                    'amount' => 3,
                    'unit_price' => 100.00,
                    'is_net' => true,
                    'is_free_text' => false,
                ],
            ],
        ];

        $results = FillOrderPositions::make($data)
            ->validate()
            ->execute();

        $this->assertIsArray($results);
        $this->assertCount(1, $results);

        // Bundle position should exist
        $this->assertDatabaseHas('order_positions', [
            'order_id' => $this->order->getKey(),
            'product_id' => $this->bundleProduct->getKey(),
            'name' => 'Bundle Position',
            'amount' => 3,
        ]);

        // Get the bundle position
        $bundlePosition = OrderPosition::where('product_id', $this->bundleProduct->getKey())->first();

        // Bundle components should be created (children array in result)
        $this->assertArrayHasKey('children', $results[0]);

        // Check component in database
        $component = $this->bundleProduct->bundleProducts->first();
        $this->assertDatabaseHas('order_positions', [
            'order_id' => $this->order->getKey(),
            'product_id' => $component->getKey(),
            'parent_id' => $bundlePosition->getKey(),
            'is_bundle_position' => 1,
            'amount' => 6, // 2 per bundle * 3 bundles
        ]);
    }

    public function test_can_fill_order_positions(): void
    {
        // Test data with one updated position and one new position
        $data = [
            'order_id' => $this->order->getKey(),
            'simulate' => false,
            'order_positions' => [
                // Update existing position
                [
                    'id' => $this->existingPosition->getKey(),
                    'order_id' => $this->order->getKey(),
                    'name' => 'Updated Position',
                    'amount' => 2,
                    'unit_price' => 100.00,
                    'is_net' => true,
                    'is_free_text' => false,
                ],
                // Add new position
                [
                    'order_id' => $this->order->getKey(),
                    'client_id' => $this->client->getKey(),
                    'product_id' => $this->product->getKey(),
                    'vat_rate_id' => $this->vatRate->getKey(),
                    'warehouse_id' => $this->warehouse->getKey(),
                    'name' => 'New Position',
                    'amount' => 3,
                    'unit_price' => 100.00,
                    'is_net' => true,
                    'is_free_text' => false,
                ],
            ],
        ];

        // Use Event::fake() to capture events
        $dispatcher = Event::fake(['order.calculating-prices', 'order.calculated-prices']);
        FluxAction::setEventDispatcher($dispatcher);

        $results = FillOrderPositions::make($data)
            ->validate()
            ->execute();

        $this->assertIsArray($results);
        $this->assertCount(2, $results);

        // Verify events were dispatched
        Event::assertDispatched('order.calculating-prices');
        Event::assertDispatched('order.calculated-prices');

        // Check updated position
        $this->assertEquals($this->existingPosition->getKey(), $results[0]['id']);
        $this->assertEquals('Updated Position', $results[0]['name']);
        $this->assertEquals(2, $results[0]['amount']);

        // Check new position
        $this->assertEquals('New Position', $results[1]['name']);
        $this->assertEquals(3, $results[1]['amount']);

        // Check database
        $this->assertDatabaseHas('order_positions', [
            'id' => $this->existingPosition->getKey(),
            'order_id' => $this->order->getKey(),
            'name' => 'Updated Position',
            'amount' => 2,
        ]);

        $this->assertDatabaseHas('order_positions', [
            'order_id' => $this->order->getKey(),
            'name' => 'New Position',
            'amount' => 3,
        ]);
    }

    public function test_can_simulate_fill_order_positions(): void
    {
        // Test data with simulation mode enabled
        $data = [
            'order_id' => $this->order->getKey(),
            'simulate' => true, // Simulation mode
            'order_positions' => [
                [
                    'id' => $this->existingPosition->getKey(),
                    'order_id' => $this->order->getKey(),
                    'unit_price' => 75.00,
                    'name' => 'Simulated Update',
                    'amount' => 5,
                ],
                [
                    'order_id' => $this->order->getKey(),
                    'client_id' => $this->client->getKey(),
                    'product_id' => $this->product->getKey(),
                    'vat_rate_id' => $this->vatRate->getKey(),
                    'warehouse_id' => $this->warehouse->getKey(),
                    'name' => 'Simulated New Position',
                    'amount' => 3,
                    'unit_price' => 75.00,
                    'is_net' => true,
                    'is_free_text' => false,
                ],
            ],
        ];

        $results = FillOrderPositions::make($data)
            ->validate()
            ->execute();

        $this->assertIsArray($results);
        $this->assertCount(2, $results);

        // Check results contain the simulated data
        $this->assertEquals($this->existingPosition->getKey(), $results[0]['id']);
        $this->assertEquals('Simulated Update', $results[0]['name']);
        $this->assertEquals(5, $results[0]['amount']);

        $this->assertEquals('Simulated New Position', $results[1]['name']);
        $this->assertEquals(3, $results[1]['amount']);

        // In simulation mode, no database changes should happen
        $this->assertDatabaseMissing('order_positions', [
            'id' => $this->existingPosition->getKey(),
            'name' => 'Simulated Update',
            'amount' => 5,
        ]);

        $this->assertDatabaseMissing('order_positions', [
            'order_id' => $this->order->getKey(),
            'name' => 'Simulated New Position',
        ]);

        // Original data should still exist unchanged
        $this->assertDatabaseHas('order_positions', [
            'id' => $this->existingPosition->getKey(),
            'name' => 'Existing Position',
            'amount' => 1,
        ]);
    }

    public function test_free_text_position_validation(): void
    {
        // Test data with free text position (doesn't need pricing)
        $data = [
            'order_id' => $this->order->getKey(),
            'simulate' => false,
            'order_positions' => [
                [
                    'order_id' => $this->order->getKey(),
                    'client_id' => $this->client->getKey(),
                    'name' => 'Free Text Position',
                    'description' => 'This is a description',
                    'is_free_text' => true,
                ],
            ],
        ];

        $results = FillOrderPositions::make($data)
            ->validate()
            ->execute();

        $this->assertIsArray($results);
        $this->assertCount(1, $results);

        // Free text position should exist
        $this->assertDatabaseHas('order_positions', [
            'order_id' => $this->order->getKey(),
            'name' => 'Free Text Position',
            'description' => 'This is a description',
            'is_free_text' => 1,
        ]);

        // Free text positions should have null pricing
        $freeTextPosition = OrderPosition::where('name', 'Free Text Position')->first();
        $this->assertNull($freeTextPosition->unit_net_price);
        $this->assertNull($freeTextPosition->unit_gross_price);
        $this->assertNull($freeTextPosition->total_net_price);
        $this->assertNull($freeTextPosition->total_gross_price);
    }

    public function test_prevents_deletion_of_positions_with_descendants(): void
    {
        // Create a parent position
        $parentPosition = OrderPosition::factory()->create([
            'client_id' => $this->client->getKey(),
            'order_id' => $this->order->getKey(),
            'name' => 'Parent With Descendants',
            'is_free_text' => false,
        ]);

        // Create a child position
        OrderPosition::factory()->create([
            'client_id' => $this->client->getKey(),
            'order_id' => $this->order->getKey(),
            'origin_position_id' => $parentPosition->getKey(),
            'name' => 'Child Position',
            'is_free_text' => false,
        ]);

        // Test data that doesn't include the parent position (which would delete it)
        $data = [
            'simulate' => false,
            'order_id' => $this->order->getKey(),
            'order_positions' => [
                [
                    'id' => $this->existingPosition->getKey(),
                    'order_id' => $this->order->getKey(),
                    'name' => 'Only This Position',
                    'unit_price' => 50.00,
                    'is_net' => true,
                    'is_free_text' => false,
                ],
            ],
        ];

        // Should throw validation exception because we're trying to delete a position with descendants
        $this->expectException(ValidationException::class);

        try {
            FillOrderPositions::make($data)
                ->validate()
                ->execute();
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('deleted_order_positions', $e->errors());
            throw $e;
        }
    }

    public function test_removes_positions_not_included_in_update(): void
    {
        // Create an additional position that will be removed
        $positionToRemove = OrderPosition::factory()->create([
            'client_id' => $this->client->getKey(),
            'order_id' => $this->order->getKey(),
            'product_id' => $this->product->getKey(),
            'name' => 'Position To Remove',
            'is_free_text' => false,
        ]);

        // Test data that only includes the existing position
        $data = [
            'order_id' => $this->order->getKey(),
            'simulate' => false,
            'order_positions' => [
                [
                    'id' => $this->existingPosition->getKey(),
                    'order_id' => $this->order->getKey(),
                    'name' => 'Kept Position',
                    'unit_price' => 100,
                    'is_net' => true,
                    'is_free_text' => false,
                ],
            ],
        ];

        $results = FillOrderPositions::make($data)
            ->validate()
            ->execute();

        $this->assertIsArray($results);
        $this->assertCount(1, $results);

        // The position that wasn't included should be removed
        $this->assertSoftDeleted('order_positions', ['id' => $positionToRemove->getKey()]);

        // The included position should remain
        $this->assertDatabaseHas('order_positions', [
            'id' => $this->existingPosition->getKey(),
            'name' => 'Kept Position',
        ]);
    }

    public function test_validates_position_pricing(): void
    {
        // Test data with invalid pricing (missing unit_price when required)
        $data = [
            'order_id' => $this->order->getKey(),
            'simulate' => false,
            'order_positions' => [
                [
                    'order_id' => $this->order->getKey(),
                    'client_id' => $this->client->getKey(),
                    'product_id' => $this->product->getKey(),
                    'vat_rate_id' => $this->vatRate->getKey(),
                    'warehouse_id' => $this->warehouse->getKey(),
                    'name' => 'Invalid Position',
                    'amount' => 1,
                    // Missing unit_price, price_id, or price_list_id
                    'is_net' => true,
                    'is_free_text' => false,
                ],
            ],
        ];

        // Should throw validation exception
        $this->expectException(ValidationException::class);

        try {
            FillOrderPositions::make($data)
                ->validate()
                ->execute();
        } catch (ValidationException $e) {
            // Check for price-related validation errors
            $this->assertArrayHasKey(0, $e->errors());
            throw $e;
        }
    }
}

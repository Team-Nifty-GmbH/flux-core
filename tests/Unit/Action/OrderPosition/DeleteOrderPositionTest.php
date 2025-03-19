<?php

namespace FluxErp\Tests\Unit\Action\OrderPosition;

use FluxErp\Actions\OrderPosition\DeleteOrderPosition;
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
use Illuminate\Validation\ValidationException;

class DeleteOrderPositionTest extends TestCase
{
    private OrderPosition $bundlePosition;

    private OrderPosition $lockedOrderPosition;

    private OrderPosition $orderPosition;

    private OrderPosition $parentPosition;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a client
        $client = Client::factory()->create(['is_default' => true]);

        // Create address/contact
        $contact = Contact::factory()
            ->has(Address::factory()->for($client))
            ->for($client)
            ->create();

        $address = $contact->addresses()->first();

        // Create required entities for order
        $orderType = OrderType::factory()->create([
            'client_id' => $client->getKey(),
            'order_type_enum' => OrderTypeEnum::Order,
        ]);

        $paymentType = PaymentType::factory()
            ->hasAttached($client, relationship: 'clients')
            ->create();

        $currency = Currency::factory()->create(['is_default' => true]);

        $priceList = PriceList::factory()->create();

        // Create a regular order
        $order = Order::factory()->create([
            'client_id' => $client->getKey(),
            'order_type_id' => $orderType->getKey(),
            'price_list_id' => $priceList->getKey(),
            'payment_type_id' => $paymentType->getKey(),
            'currency_id' => $currency->getKey(),
            'address_invoice_id' => $address->getKey(),
            'address_delivery_id' => $address->getKey(),
            'is_locked' => false,
        ]);

        // Create a locked order
        $lockedOrder = Order::factory()->create([
            'client_id' => $client->getKey(),
            'order_type_id' => $orderType->getKey(),
            'price_list_id' => $priceList->getKey(),
            'payment_type_id' => $paymentType->getKey(),
            'currency_id' => $currency->getKey(),
            'address_invoice_id' => $address->getKey(),
            'address_delivery_id' => $address->getKey(),
            'is_locked' => true,
        ]);

        // Create products and warehouse
        $vatRate = VatRate::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create([
            'client_id' => $client->getKey(),
            'vat_rate_id' => $vatRate->getKey(),
        ]);

        // Create a regular order position
        $this->orderPosition = OrderPosition::factory()->create([
            'client_id' => $client->getKey(),
            'order_id' => $order->getKey(),
            'product_id' => $product->getKey(),
            'vat_rate_id' => $vatRate->getKey(),
            'warehouse_id' => $warehouse->getKey(),
            'name' => 'Regular Position',
            'amount' => 1,
            'is_free_text' => false,
            'is_bundle_position' => false,
        ]);

        // Create a position in locked order
        $this->lockedOrderPosition = OrderPosition::factory()->create([
            'client_id' => $client->getKey(),
            'order_id' => $lockedOrder->getKey(),
            'product_id' => $product->getKey(),
            'vat_rate_id' => $vatRate->getKey(),
            'warehouse_id' => $warehouse->getKey(),
            'name' => 'Position in Locked Order',
            'amount' => 1,
            'is_free_text' => false,
            'is_bundle_position' => false,
        ]);

        // Create a bundle position
        $this->bundlePosition = OrderPosition::factory()->create([
            'client_id' => $client->getKey(),
            'order_id' => $order->getKey(),
            'product_id' => $product->getKey(),
            'vat_rate_id' => $vatRate->getKey(),
            'warehouse_id' => $warehouse->getKey(),
            'name' => 'Bundle Position',
            'amount' => 1,
            'is_free_text' => false,
            'is_bundle_position' => true,
        ]);

        // Create a parent position with children
        $this->parentPosition = OrderPosition::factory()->create([
            'client_id' => $client->getKey(),
            'order_id' => $order->getKey(),
            'product_id' => $product->getKey(),
            'vat_rate_id' => $vatRate->getKey(),
            'warehouse_id' => $warehouse->getKey(),
            'name' => 'Parent Position',
            'amount' => 1,
            'is_free_text' => false,
            'is_bundle_position' => false,
        ]);

        // Create a child position
        OrderPosition::factory()->create([
            'client_id' => $client->getKey(),
            'order_id' => $order->getKey(),
            'product_id' => $product->getKey(),
            'parent_id' => $this->parentPosition->getKey(),
            'name' => 'Child Position',
            'is_bundle_position' => true,
        ]);
    }

    public function test_can_delete_order_position(): void
    {
        $this->assertDatabaseHas('order_positions', ['id' => $this->orderPosition->getKey()]);

        $result = DeleteOrderPosition::make(['id' => $this->orderPosition->getKey()])
            ->validate()
            ->execute();

        $this->assertTrue($result);
        $this->assertSoftDeleted('order_positions', ['id' => $this->orderPosition->getKey()]);

        // Soft delete should have been applied
        $this->assertSoftDeleted('order_positions', ['id' => $this->orderPosition->getKey()]);
    }

    public function test_cannot_delete_bundle_position(): void
    {
        $this->assertDatabaseHas('order_positions', ['id' => $this->bundlePosition->getKey()]);
        $this->assertTrue($this->bundlePosition->is_bundle_position);

        // Should throw validation exception
        $this->expectException(ValidationException::class);

        try {
            DeleteOrderPosition::make(['id' => $this->bundlePosition->getKey()])
                ->validate()
                ->execute();
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('is_bundle_position', $e->errors());
            throw $e;
        }

        // Position should still exist
        $this->assertDatabaseHas('order_positions', ['id' => $this->bundlePosition->getKey()]);
    }

    public function test_cannot_delete_position_from_locked_order(): void
    {
        $this->assertDatabaseHas('order_positions', ['id' => $this->lockedOrderPosition->getKey()]);

        // Should throw validation exception
        $this->expectException(ValidationException::class);

        try {
            DeleteOrderPosition::make(['id' => $this->lockedOrderPosition->getKey()])
                ->validate()
                ->execute();
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('is_locked', $e->errors());
            throw $e;
        }

        // Position should still exist
        $this->assertDatabaseHas('order_positions', ['id' => $this->lockedOrderPosition->getKey()]);
    }

    public function test_deletes_child_positions(): void
    {
        $this->assertDatabaseHas('order_positions', ['id' => $this->parentPosition->getKey()]);
        $this->assertDatabaseHas('order_positions', ['parent_id' => $this->parentPosition->getKey()]);

        $result = DeleteOrderPosition::make(['id' => $this->parentPosition->getKey()])
            ->validate()
            ->execute();

        $this->assertTrue($result);

        // Parent should be deleted
        $this->assertSoftDeleted('order_positions', ['id' => $this->parentPosition->getKey()]);

        // Child positions should also be deleted
        $this->assertSoftDeleted('order_positions', ['parent_id' => $this->parentPosition->getKey()]);
    }

    public function test_throws_error_when_position_not_found(): void
    {
        // Should throw validation exception
        $this->expectException(ValidationException::class);

        try {
            DeleteOrderPosition::make(['id' => 99999]) // Non-existent ID
                ->validate()
                ->execute();
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('id', $e->errors());
            throw $e;
        }
    }
}

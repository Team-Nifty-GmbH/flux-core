<?php

namespace FluxErp\Tests\Livewire\Order;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Order\CreateChildOrder;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\Unit;
use FluxErp\Models\VatRate;
use FluxErp\Models\Warehouse;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class CreateChildOrderTest extends BaseSetup
{
    private Order $parentOrder;

    private OrderType $retoureOrderType;

    private OrderType $splitOrderType;

    protected function setUp(): void
    {
        parent::setUp();

        $contact = Contact::factory()->create([
            'client_id' => $this->dbClient->getKey(),
        ]);

        $address = Address::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'contact_id' => $contact->id,
        ]);

        $currency = Currency::factory()->create();
        $priceList = PriceList::factory()->create();
        $paymentType = PaymentType::factory()
            ->hasAttached(factory: $this->dbClient, relationship: 'clients')
            ->create();
        $vatRate = VatRate::factory()->create();
        $language = Language::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $orderType = OrderType::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'order_type_enum' => OrderTypeEnum::Order,
            'is_active' => true,
        ]);

        $this->retoureOrderType = OrderType::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'order_type_enum' => OrderTypeEnum::Retoure,
            'is_active' => true,
        ]);

        $this->splitOrderType = OrderType::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'order_type_enum' => OrderTypeEnum::SplitOrder,
            'is_active' => true,
            'is_hidden' => false,
        ]);

        $this->parentOrder = Order::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'contact_id' => $contact->id,
            'order_type_id' => $orderType->id,
            'address_invoice_id' => $address->id,
            'address_delivery_id' => $address->id,
            'currency_id' => $currency->id,
            'price_list_id' => $priceList->id,
            'payment_type_id' => $paymentType->id,
            'language_id' => $language->id,
            'warehouse_id' => $warehouse->id,
            'invoice_date' => now(),
        ]);

        $unit = Unit::factory()->create();
        $product = Product::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'unit_id' => $unit->id,
        ]);

        OrderPosition::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'order_id' => $this->parentOrder->id,
            'product_id' => $product->id,
            'vat_rate_id' => $vatRate->id,
            'amount' => 10,
            'unit_net_price' => 100,
            'unit_gross_price' => 119,
            'total_net_price' => 1000,
            'total_gross_price' => 1190,
            'is_net' => false,
        ]);
    }

    public function test_can_remove_position(): void
    {
        $orderPosition = $this->parentOrder->orderPositions()->first();

        $component = Livewire::test(CreateChildOrder::class, [
            'orderId' => $this->parentOrder->id,
            'type' => OrderTypeEnum::Retoure->value,
        ]);

        // Add position first
        $component->set('selectedPositions', [$orderPosition->id])
            ->call('takeOrderPositions');

        // Then remove it
        $component->call('removePosition', 0)
            ->assertSet('replicateOrder.order_positions', []);
    }

    public function test_can_render_retoure_creation(): void
    {
        $component = Livewire::test(CreateChildOrder::class, [
            'orderId' => $this->parentOrder->id,
            'type' => OrderTypeEnum::Retoure->value,
        ]);

        $component->assertOk()
            ->assertSee(__('Create Retoure'))
            ->assertSee(__('Parent Order'))
            ->assertSee($this->parentOrder->order_number)
            ->assertSee(__('Available Positions'))
            ->assertSee(__('Selected Positions'));
    }

    public function test_can_render_split_order_creation(): void
    {
        // Split orders can only be created from non-invoiced orders
        $this->parentOrder->update(['invoice_date' => null]);

        $component = Livewire::test(CreateChildOrder::class, [
            'orderId' => $this->parentOrder->id,
            'type' => OrderTypeEnum::SplitOrder->value,
        ]);

        $component->assertOk()
            ->assertSee(__('Create Split-Order'))
            ->assertSee(__('Parent Order'))
            ->assertSee($this->parentOrder->order_number)
            ->assertSee(__('Available Positions'))
            ->assertSee(__('Selected Positions'));
    }

    public function test_can_save_retoure(): void
    {
        $orderPosition = $this->parentOrder->orderPositions()->first();

        $component = Livewire::test(CreateChildOrder::class, [
            'orderId' => $this->parentOrder->id,
            'type' => OrderTypeEnum::Retoure->value,
        ]);

        $component->set('selectedPositions', [$orderPosition->id])
            ->call('takeOrderPositions')
            ->call('save')
            ->assertHasNoErrors();

        $createdOrder = Order::query()
            ->where('parent_id', $this->parentOrder->id)
            ->first();

        $this->assertNotNull($createdOrder, 'Child order should be created');
        $this->assertEquals($this->retoureOrderType->id, $createdOrder->order_type_id);

        $component->assertRedirect(route('orders.id', ['id' => $createdOrder->id]));
    }

    public function test_can_save_split_order(): void
    {
        // Split orders can only be created from non-invoiced orders
        $this->parentOrder->update(['invoice_date' => null]);

        $orderPosition = $this->parentOrder->orderPositions()->first();

        $component = Livewire::test(CreateChildOrder::class, [
            'orderId' => $this->parentOrder->id,
            'type' => OrderTypeEnum::SplitOrder->value,
        ]);

        // Add position and save
        $component->set('selectedPositions', [$orderPosition->id])
            ->call('takeOrderPositions')
            ->call('save')
            ->assertHasNoErrors();

        // Check that a new order was created
        $createdOrder = Order::query()
            ->where('parent_id', $this->parentOrder->id)
            ->first();
        $this->assertNotNull($createdOrder, 'Child order should be created');
        $this->assertEquals($this->splitOrderType->id, $createdOrder->order_type_id);

        $component->assertRedirect(route('orders.id', ['id' => $createdOrder->id]));
    }

    public function test_can_take_order_positions(): void
    {
        $orderPosition = $this->parentOrder->orderPositions()->first();

        $component = Livewire::test(CreateChildOrder::class, [
            'orderId' => $this->parentOrder->id,
            'type' => OrderTypeEnum::Retoure->value,
        ]);

        $component->set('selectedPositions', [$orderPosition->id])
            ->call('takeOrderPositions')
            ->assertSet('selectedPositions', [])
            ->assertSet('replicateOrder.order_positions.0.id', $orderPosition->id)
            ->assertSet('replicateOrder.order_positions.0.name', $orderPosition->name);
    }

    public function test_cannot_save_without_positions(): void
    {
        $component = Livewire::test(CreateChildOrder::class, [
            'orderId' => $this->parentOrder->id,
            'type' => OrderTypeEnum::Retoure->value,
        ]);

        $component->call('save')
            ->assertHasErrors(); // The component should have validation errors
    }

    public function test_prevents_duplicate_position_selection(): void
    {
        $orderPosition = $this->parentOrder->orderPositions()->first();

        $component = Livewire::test(CreateChildOrder::class, [
            'orderId' => $this->parentOrder->id,
            'type' => OrderTypeEnum::Retoure->value,
        ]);

        // Add position first time
        $component->set('selectedPositions', [$orderPosition->id])
            ->call('takeOrderPositions')
            ->assertCount('replicateOrder.order_positions', 1);

        // Try to add same position again
        $component->set('selectedPositions', [$orderPosition->id])
            ->call('takeOrderPositions')
            ->assertCount('replicateOrder.order_positions', 1); // Should still be 1
    }

    public function test_redirects_with_invalid_order_id(): void
    {
        $component = Livewire::test(CreateChildOrder::class, [
            'orderId' => 999999,
            'type' => OrderTypeEnum::Retoure->value,
        ]);

        $component->assertRedirect(route('orders.orders'));
    }

    public function test_redirects_with_invalid_type(): void
    {
        $component = Livewire::test(CreateChildOrder::class, [
            'orderId' => $this->parentOrder->id,
            'type' => 'invalid-type',
        ]);

        $component->assertRedirectToRoute('orders.orders');
    }

    public function test_redirects_without_parameters(): void
    {
        $component = Livewire::test(CreateChildOrder::class);

        $component->assertRedirectToRoute('orders.orders');
    }

    public function test_shows_correct_title_for_retoure(): void
    {
        $component = Livewire::test(CreateChildOrder::class, [
            'orderId' => $this->parentOrder->id,
            'type' => OrderTypeEnum::Retoure->value,
        ]);

        $this->assertEquals(__('Create Retoure'), $component->instance()->getTitle());
    }

    public function test_shows_correct_title_for_split_order(): void
    {
        $component = Livewire::test(CreateChildOrder::class, [
            'orderId' => $this->parentOrder->id,
            'type' => OrderTypeEnum::SplitOrder->value,
        ]);

        $this->assertEquals(__('Create Split-Order'), $component->instance()->getTitle());
    }
}

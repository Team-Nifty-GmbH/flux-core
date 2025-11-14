<?php

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
use Livewire\Livewire;

beforeEach(function (): void {
    $contact = Contact::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $address = Address::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->id,
    ]);

    $currency = Currency::factory()->create();
    $priceList = PriceList::factory()->create();
    $paymentType = PaymentType::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();
    $vatRate = VatRate::factory()->create();
    $language = Language::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $orderType = OrderType::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);

    $this->retoureOrderType = OrderType::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_type_enum' => OrderTypeEnum::Retoure,
        'is_active' => true,
    ]);

    $this->splitOrderType = OrderType::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_type_enum' => OrderTypeEnum::SplitOrder,
        'is_active' => true,
        'is_hidden' => false,
    ]);

    $this->parentOrder = Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
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
        'tenant_id' => $this->dbTenant->getKey(),
        'unit_id' => $unit->id,
    ]);

    OrderPosition::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
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
});

test('can remove position', function (): void {
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
});

test('can render retoure creation', function (): void {
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
});

test('can render split order creation', function (): void {
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
});

test('can save retoure', function (): void {
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

    expect($createdOrder)->not->toBeNull('Child order should be created');
    expect($createdOrder->order_type_id)->toEqual($this->retoureOrderType->id);

    $component->assertRedirect(route('orders.id', ['id' => $createdOrder->id]));
});

test('can save split order', function (): void {
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
    expect($createdOrder)->not->toBeNull('Child order should be created');
    expect($createdOrder->order_type_id)->toEqual($this->splitOrderType->id);

    $component->assertRedirect(route('orders.id', ['id' => $createdOrder->id]));
});

test('can take order positions', function (): void {
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
});

test('cannot save without positions', function (): void {
    $component = Livewire::test(CreateChildOrder::class, [
        'orderId' => $this->parentOrder->id,
        'type' => OrderTypeEnum::Retoure->value,
    ]);

    $component->call('save')
        ->assertHasErrors();
    // The component should have validation errors
});

test('prevents duplicate position selection', function (): void {
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
        ->assertCount('replicateOrder.order_positions', 1);
    // Should still be 1
});

test('redirects with invalid order id', function (): void {
    $component = Livewire::test(CreateChildOrder::class, [
        'orderId' => 999999,
        'type' => OrderTypeEnum::Retoure->value,
    ]);

    $component->assertRedirect(route('orders.orders'));
});

test('redirects with invalid type', function (): void {
    $component = Livewire::test(CreateChildOrder::class, [
        'orderId' => $this->parentOrder->id,
        'type' => 'invalid-type',
    ]);

    $component->assertRedirectToRoute('orders.orders');
});

test('redirects without parameters', function (): void {
    $component = Livewire::test(CreateChildOrder::class);

    $component->assertRedirectToRoute('orders.orders');
});

test('shows correct title for retoure', function (): void {
    $component = Livewire::test(CreateChildOrder::class, [
        'orderId' => $this->parentOrder->id,
        'type' => OrderTypeEnum::Retoure->value,
    ]);

    expect($component->instance()->getTitle())->toEqual(__('Create Retoure'));
});

test('shows correct title for split order', function (): void {
    $component = Livewire::test(CreateChildOrder::class, [
        'orderId' => $this->parentOrder->id,
        'type' => OrderTypeEnum::SplitOrder->value,
    ]);

    expect($component->instance()->getTitle())->toEqual(__('Create Split-Order'));
});

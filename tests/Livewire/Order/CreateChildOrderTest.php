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
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function (): void {
    $contact = Contact::factory()->create();

    $address = Address::factory()->create([
        'contact_id' => $contact->id,
    ]);

    $currency = Currency::factory()->create();
    $priceList = PriceList::factory()->create();
    $paymentType = PaymentType::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();
    $vatRate = VatRate::factory()->create();
    $language = Language::factory()->create();

    $orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);

    $this->retoureOrderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Retoure,
        'is_active' => true,
    ]);

    $this->splitOrderType = OrderType::factory()->create([
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
        'invoice_date' => now(),
    ]);

    $unit = Unit::factory()->create();
    $product = Product::factory()->create([
        'unit_id' => $unit->id,
    ]);
    $product->tenants()->attach($this->dbTenant->getKey());

    $warehouse = Warehouse::factory()->create();
    OrderPosition::factory()->create([
        'order_id' => $this->parentOrder->id,
        'product_id' => $product->id,
        'tenant_id' => $this->dbTenant->getKey(),
        'vat_rate_id' => $vatRate->id,
        'warehouse_id' => $warehouse->getKey(),
        'amount' => 10,
        'signed_amount' => 10,
        'unit_net_price' => 100,
        'unit_gross_price' => 119,
        'total_net_price' => 1000,
        'total_gross_price' => 1190,
        'is_net' => false,
        'is_free_text' => false,
        'is_alternative' => false,
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
    $this->parentOrder->update(['invoice_number' => null]);

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
    $this->parentOrder->update(['invoice_number' => Str::random()]);
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
    $this->parentOrder->update(['invoice_number' => null]);

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

test('can take free text positions', function (): void {
    $vatRate = VatRate::factory()->create();

    $freeText = OrderPosition::factory()->create([
        'order_id' => $this->parentOrder->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'name' => 'Free Text Note',
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => true,
        'is_alternative' => false,
    ]);

    $component = Livewire::test(CreateChildOrder::class, [
        'orderId' => $this->parentOrder->getKey(),
        'type' => OrderTypeEnum::Retoure->value,
    ]);

    $component->set('selectedPositions', [$freeText->getKey()])
        ->call('takeOrderPositions');

    $positions = $component->get('replicateOrder.order_positions');
    $freeTextPosition = collect($positions)->firstWhere('id', $freeText->getKey());

    expect($freeTextPosition)->not->toBeNull()
        ->and($freeTextPosition['name'])->toBe('Free Text Note');
});

test('can take mix of real and free text positions', function (): void {
    $vatRate = VatRate::factory()->create();
    $realPosition = $this->parentOrder->orderPositions()->first();

    $freeText = OrderPosition::factory()->create([
        'order_id' => $this->parentOrder->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'name' => 'Section Header',
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => true,
        'is_alternative' => false,
    ]);

    $component = Livewire::test(CreateChildOrder::class, [
        'orderId' => $this->parentOrder->getKey(),
        'type' => OrderTypeEnum::Retoure->value,
    ]);

    $component->set('selectedPositions', [$freeText->getKey(), $realPosition->getKey()])
        ->call('takeOrderPositions');

    $positions = $component->get('replicateOrder.order_positions');

    expect($positions)->toHaveCount(2);

    $names = collect($positions)->pluck('name')->toArray();
    expect($names)->toContain('Section Header')
        ->and($names)->toContain($realPosition->name);
});

test('takeOrderPositions dispatches updateAlreadyTakenPositions', function (): void {
    $orderPosition = $this->parentOrder->orderPositions()->first();

    Livewire::test(CreateChildOrder::class, [
        'orderId' => $this->parentOrder->getKey(),
        'type' => OrderTypeEnum::Retoure->value,
    ])
        ->set('selectedPositions', [$orderPosition->getKey()])
        ->call('takeOrderPositions')
        ->assertDispatched('updateAlreadyTakenPositions', fn ($name, $params) => in_array($orderPosition->getKey(), $params['alreadyTakenPositions'])
        );
});

test('removePosition dispatches updateAlreadyTakenPositions', function (): void {
    $orderPosition = $this->parentOrder->orderPositions()->first();

    $component = Livewire::test(CreateChildOrder::class, [
        'orderId' => $this->parentOrder->getKey(),
        'type' => OrderTypeEnum::Retoure->value,
    ])
        ->set('selectedPositions', [$orderPosition->getKey()])
        ->call('takeOrderPositions');

    $component->call('removePosition', 0)
        ->assertDispatched('updateAlreadyTakenPositions', fn ($name, $params) => empty($params['alreadyTakenPositions'])
        );
});

test('taken positions follow tree order regardless of selection order', function (): void {
    $vatRate = VatRate::factory()->create();
    $existingPosition = $this->parentOrder->orderPositions()->where('is_free_text', false)->first();

    // Create a second real position
    $secondPosition = OrderPosition::factory()->create([
        'order_id' => $this->parentOrder->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'name' => 'Second Position',
        'amount' => 3,
        'signed_amount' => 3,
        'unit_net_price' => 50,
        'unit_gross_price' => 59.50,
        'total_net_price' => 150,
        'total_gross_price' => 178.50,
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => false,
        'is_alternative' => false,
    ]);

    $component = Livewire::test(CreateChildOrder::class, [
        'orderId' => $this->parentOrder->getKey(),
        'type' => OrderTypeEnum::Retoure->value,
    ]);

    // Select in reverse order
    $component->set('selectedPositions', [$secondPosition->getKey(), $existingPosition->getKey()])
        ->call('takeOrderPositions');

    $positions = $component->get('replicateOrder.order_positions');
    $ids = array_column($positions, 'id');

    // Tree order: existing (lower ID) before second (higher ID)
    expect(array_search($existingPosition->getKey(), $ids))
        ->toBeLessThan(array_search($secondPosition->getKey(), $ids));
});

test('selecting child position auto-includes parent block', function (): void {
    $vatRate = VatRate::factory()->create();

    $block = OrderPosition::factory()->create([
        'order_id' => $this->parentOrder->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'name' => 'AutoBlock',
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => true,
        'is_alternative' => false,
    ]);

    $child = OrderPosition::factory()->create([
        'order_id' => $this->parentOrder->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'parent_id' => $block->getKey(),
        'name' => 'BlockChild',
        'amount' => 2,
        'signed_amount' => 2,
        'unit_net_price' => 100,
        'unit_gross_price' => 119,
        'total_net_price' => 200,
        'total_gross_price' => 238,
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => false,
        'is_alternative' => false,
    ]);

    $component = Livewire::test(CreateChildOrder::class, [
        'orderId' => $this->parentOrder->getKey(),
        'type' => OrderTypeEnum::Retoure->value,
    ]);

    // Only select the child — block should be auto-included
    $component->set('selectedPositions', [$child->getKey()])
        ->call('takeOrderPositions');

    $positions = $component->get('replicateOrder.order_positions');
    $ids = array_column($positions, 'id');

    expect($ids)->toContain($block->getKey())
        ->and($ids)->toContain($child->getKey());
});

test('block stays visible on left when only some children are taken', function (): void {
    $vatRate = VatRate::factory()->create();

    $block = OrderPosition::factory()->create([
        'order_id' => $this->parentOrder->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'name' => 'PartialBlock',
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => true,
        'is_alternative' => false,
    ]);

    $child1 = OrderPosition::factory()->create([
        'order_id' => $this->parentOrder->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'parent_id' => $block->getKey(),
        'name' => 'TakenChild',
        'amount' => 2,
        'signed_amount' => 2,
        'unit_net_price' => 100,
        'unit_gross_price' => 119,
        'total_net_price' => 200,
        'total_gross_price' => 238,
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => false,
        'is_alternative' => false,
    ]);

    OrderPosition::factory()->create([
        'order_id' => $this->parentOrder->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'parent_id' => $block->getKey(),
        'name' => 'RemainingChild',
        'amount' => 3,
        'signed_amount' => 3,
        'unit_net_price' => 50,
        'unit_gross_price' => 59.50,
        'total_net_price' => 150,
        'total_gross_price' => 178.50,
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => false,
        'is_alternative' => false,
    ]);

    $component = Livewire::test(CreateChildOrder::class, [
        'orderId' => $this->parentOrder->getKey(),
        'type' => OrderTypeEnum::Retoure->value,
    ]);

    // Take only child1
    $component->set('selectedPositions', [$child1->getKey()])
        ->call('takeOrderPositions');

    // alreadyTakenPositions must NOT contain the block ID
    // so the block stays visible on the left with remaining children
    $dispatched = $component->effects['dispatches'] ?? [];
    $updateEvent = collect($dispatched)->firstWhere('name', 'updateAlreadyTakenPositions');
    $takenIds = data_get($updateEvent, 'params.alreadyTakenPositions', []);

    expect($takenIds)->toContain($child1->getKey())
        ->and($takenIds)->not->toContain($block->getKey());
});

test('removing block removes all its children from right list', function (): void {
    $vatRate = VatRate::factory()->create();

    $block = OrderPosition::factory()->create([
        'order_id' => $this->parentOrder->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'name' => 'RemovableBlock',
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => true,
        'is_alternative' => false,
    ]);

    $child = OrderPosition::factory()->create([
        'order_id' => $this->parentOrder->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'parent_id' => $block->getKey(),
        'name' => 'BlockChildToRemove',
        'amount' => 2,
        'signed_amount' => 2,
        'unit_net_price' => 100,
        'unit_gross_price' => 119,
        'total_net_price' => 200,
        'total_gross_price' => 238,
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => false,
        'is_alternative' => false,
    ]);

    $component = Livewire::test(CreateChildOrder::class, [
        'orderId' => $this->parentOrder->getKey(),
        'type' => OrderTypeEnum::Retoure->value,
    ]);

    // Take child (block auto-included)
    $component->set('selectedPositions', [$child->getKey()])
        ->call('takeOrderPositions');

    $positions = $component->get('replicateOrder.order_positions');
    expect($positions)->toHaveCount(2);

    // Find block index and remove it
    $blockIndex = array_search(
        $block->getKey(),
        array_column($positions, 'id')
    );

    $component->call('removePosition', $blockIndex);

    // Both block and child should be gone
    $remaining = $component->get('replicateOrder.order_positions');
    $remainingIds = array_column($remaining, 'id');

    expect($remainingIds)->not->toContain($block->getKey())
        ->and($remainingIds)->not->toContain($child->getKey());
});

test('nested blocks: selecting deep child includes all ancestor blocks', function (): void {
    $vatRate = VatRate::factory()->create();

    $outerBlock = OrderPosition::factory()->create([
        'order_id' => $this->parentOrder->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'name' => 'OuterBlock',
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => true,
        'is_alternative' => false,
    ]);

    $innerBlock = OrderPosition::factory()->create([
        'order_id' => $this->parentOrder->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'parent_id' => $outerBlock->getKey(),
        'name' => 'InnerBlock',
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => true,
        'is_alternative' => false,
    ]);

    $deepChild = OrderPosition::factory()->create([
        'order_id' => $this->parentOrder->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'parent_id' => $innerBlock->getKey(),
        'name' => 'DeepChild',
        'amount' => 1,
        'signed_amount' => 1,
        'unit_net_price' => 200,
        'unit_gross_price' => 238,
        'total_net_price' => 200,
        'total_gross_price' => 238,
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => false,
        'is_alternative' => false,
    ]);

    $component = Livewire::test(CreateChildOrder::class, [
        'orderId' => $this->parentOrder->getKey(),
        'type' => OrderTypeEnum::Retoure->value,
    ]);

    // Select only the deep child
    $component->set('selectedPositions', [$deepChild->getKey()])
        ->call('takeOrderPositions');

    $positions = $component->get('replicateOrder.order_positions');
    $ids = array_column($positions, 'id');

    expect($ids)->toContain($outerBlock->getKey())
        ->and($ids)->toContain($innerBlock->getKey())
        ->and($ids)->toContain($deepChild->getKey());
});

test('nested blocks: removing outer block removes everything inside', function (): void {
    $vatRate = VatRate::factory()->create();

    $outerBlock = OrderPosition::factory()->create([
        'order_id' => $this->parentOrder->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'name' => 'OuterToRemove',
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => true,
        'is_alternative' => false,
    ]);

    $innerBlock = OrderPosition::factory()->create([
        'order_id' => $this->parentOrder->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'parent_id' => $outerBlock->getKey(),
        'name' => 'InnerToRemove',
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => true,
        'is_alternative' => false,
    ]);

    $deepChild = OrderPosition::factory()->create([
        'order_id' => $this->parentOrder->getKey(),
        'vat_rate_id' => $vatRate->getKey(),
        'parent_id' => $innerBlock->getKey(),
        'name' => 'DeepChildToRemove',
        'amount' => 1,
        'signed_amount' => 1,
        'unit_net_price' => 200,
        'unit_gross_price' => 238,
        'total_net_price' => 200,
        'total_gross_price' => 238,
        'tenant_id' => $this->dbTenant->getKey(),
        'is_free_text' => false,
        'is_alternative' => false,
    ]);

    $component = Livewire::test(CreateChildOrder::class, [
        'orderId' => $this->parentOrder->getKey(),
        'type' => OrderTypeEnum::Retoure->value,
    ]);

    $component->set('selectedPositions', [$deepChild->getKey()])
        ->call('takeOrderPositions');

    $positions = $component->get('replicateOrder.order_positions');
    $outerIndex = array_search($outerBlock->getKey(), array_column($positions, 'id'));

    // Remove outer block — everything inside must go
    $component->call('removePosition', $outerIndex);

    $remaining = $component->get('replicateOrder.order_positions');
    $remainingIds = array_column($remaining, 'id');

    expect($remainingIds)->not->toContain($outerBlock->getKey())
        ->and($remainingIds)->not->toContain($innerBlock->getKey())
        ->and($remainingIds)->not->toContain($deepChild->getKey());
});

test('can take all positions with wildcard selection', function (): void {
    $component = Livewire::test(CreateChildOrder::class, [
        'orderId' => $this->parentOrder->getKey(),
        'type' => OrderTypeEnum::Retoure->value,
    ]);

    $component->set('selectedPositions', ['*'])
        ->call('takeOrderPositions');

    $positions = $component->get('replicateOrder.order_positions');

    expect($positions)->not->toBeEmpty();
});

test('shows correct title for split order', function (): void {
    $component = Livewire::test(CreateChildOrder::class, [
        'orderId' => $this->parentOrder->id,
        'type' => OrderTypeEnum::SplitOrder->value,
    ]);

    expect($component->instance()->getTitle())->toEqual(__('Create Split-Order'));
});

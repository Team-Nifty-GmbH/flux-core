<?php

use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Actions\OrderPosition\DeleteOrderPosition;
use FluxErp\Actions\OrderPosition\UpdateOrderPosition;
use FluxErp\Actions\ProductConfigurationGroupItem\DeleteProductConfigurationGroupItem;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\PriceList;
use FluxErp\Models\StockPosting;
use FluxErp\Models\VatRate;

require_once __DIR__ . '/Helpers.php';

beforeEach(function (): void {
    $this->vatRate = VatRate::factory()->create(['rate_percentage' => 0.19]);
    $this->priceList = PriceList::factory()->create(['is_net' => true]);
    $this->order = configurationOrder($this->priceList);

    $this->server = configurationProduct(['is_configurable' => true, 'vat_rate_id' => $this->vatRate->getKey()]);
    configurationPrice($this->server, $this->priceList, '100');
    $this->storage = configurationGroup(['name' => 'Storage']);
    $this->ssdProduct = configurationProduct(['name' => 'SSD', 'vat_rate_id' => $this->vatRate->getKey()]);
    configurationPrice($this->ssdProduct, $this->priceList, '500');
    $this->ssd = configurationItem($this->storage, $this->ssdProduct, ['surcharge' => 10, 'max_amount' => 4]);
    $this->hdd = configurationItem($this->storage, configurationProduct(['name' => 'HDD']), ['surcharge' => 5]);
    attachConfigurationGroup($this->server, $this->storage);

    $this->parent = CreateOrderPosition::make([
        'order_id' => $this->order->getKey(),
        'product_id' => $this->server->getKey(),
        'amount' => 1,
        'configuration' => [['product_configuration_group_item_id' => $this->ssd->getKey(), 'amount' => 2]],
    ])->validate()->execute();
    $this->child = OrderPosition::query()->where('parent_id', $this->parent->getKey())->sole();
});

test('changing the parent amount scales children and keeps the surcharge price', function (): void {
    UpdateOrderPosition::make(['id' => $this->parent->getKey(), 'amount' => 3])->validate()->execute();

    $child = $this->child->refresh();
    $this->order->calculatePrices()->save();

    expect(bccomp($child->amount, '6', 2))->toBe(0)
        ->and(bccomp($child->unit_net_price, '10', 2))->toBe(0)
        ->and(bccomp($child->total_net_price, '60', 2))->toBe(0)
        ->and(bccomp($this->order->refresh()->total_net_price, '360', 2))->toBe(0);
});

test('a new configuration replaces all children', function (): void {
    UpdateOrderPosition::make([
        'id' => $this->parent->getKey(),
        'configuration' => [['product_configuration_group_item_id' => $this->hdd->getKey(), 'amount' => 1]],
    ])->validate()->execute();

    expect($this->parent->refresh()->currentConfiguration())
        ->toBe([['product_configuration_group_item_id' => $this->hdd->getKey(), 'amount' => 1]]);
    $this->assertSoftDeleted('order_positions', ['id' => $this->child->getKey()]);
});

test('a locked order cannot change its configuration', function (): void {
    $this->order->update(['is_locked' => true]);

    UpdateOrderPosition::assertValidationErrors([
        'id' => $this->parent->getKey(),
        'configuration' => [['product_configuration_group_item_id' => $this->hdd->getKey(), 'amount' => 1]],
    ], 'is_locked');
});

test('the configuration is fixed once a child posted stock', function (): void {
    StockPosting::factory()->create([
        'order_position_id' => $this->child->getKey(),
        'product_id' => $this->child->product_id,
        'warehouse_id' => $this->child->warehouse_id,
        'posting' => -2,
        'remaining_stock' => 0,
        'reserved_stock' => 0,
        'purchase_price' => 10,
    ]);

    UpdateOrderPosition::assertValidationErrors([
        'id' => $this->parent->getKey(),
        'configuration' => [['product_configuration_group_item_id' => $this->hdd->getKey(), 'amount' => 1]],
    ], 'configuration');
});

test('children cannot be deleted, moved or swapped on their own', function (): void {
    DeleteOrderPosition::assertValidationErrors(['id' => $this->child->getKey()], 'parent_id');

    UpdateOrderPosition::assertValidationErrors([
        'id' => $this->child->getKey(),
        'parent_id' => null,
    ], 'parent_id');

    UpdateOrderPosition::assertValidationErrors([
        'id' => $this->child->getKey(),
        'product_id' => $this->hdd->product_id,
    ], 'parent_id');
});

test('the product of a configured position cannot change', function (): void {
    UpdateOrderPosition::assertValidationErrors([
        'id' => $this->parent->getKey(),
        'product_id' => $this->ssdProduct->getKey(),
    ], 'product_id');
});

test('a numeric string product id equal to the current one counts as unchanged', function (): void {
    $position = UpdateOrderPosition::make([
        'id' => $this->parent->getKey(),
        'product_id' => (string) $this->parent->product_id,
        'amount' => 2,
    ])->validate()->execute();

    expect($position->product_id)->toBe($this->parent->product_id);
});

test('configuration on a normal position is rejected', function (): void {
    $normal = CreateOrderPosition::make([
        'order_id' => $this->order->getKey(),
        'product_id' => $this->ssdProduct->getKey(),
        'amount' => 1,
    ])->validate()->execute();

    UpdateOrderPosition::assertValidationErrors([
        'id' => $normal->getKey(),
        'configuration' => [['product_configuration_group_item_id' => $this->ssd->getKey(), 'amount' => 1]],
    ], 'configuration');
});

test('deleting the parent removes its children', function (): void {
    DeleteOrderPosition::make(['id' => $this->parent->getKey()])->validate()->execute();

    $this->assertSoftDeleted('order_positions', ['id' => $this->child->getKey()]);
});

test('editing a configured position after one of its options was deleted', function (): void {
    DeleteProductConfigurationGroupItem::make(['id' => $this->ssd->getKey()])->validate()->execute();
    $orphan = $this->child->refresh();

    UpdateOrderPosition::make(['id' => $this->parent->getKey(), 'amount' => 2])->validate()->execute();

    expect($orphan->product_configuration_group_item_id)->toBeNull()
        ->and(bccomp($orphan->refresh()->amount, '4', 2))->toBe(0)
        ->and($this->parent->refresh()->currentConfiguration())->toBe([]);

    UpdateOrderPosition::make([
        'id' => $this->parent->getKey(),
        'configuration' => [['product_configuration_group_item_id' => $this->hdd->getKey(), 'amount' => 1]],
    ])->validate()->execute();

    expect(OrderPosition::query()->where('parent_id', $this->parent->getKey())->pluck('product_id')->all())
        ->toBe([$this->hdd->product_id]);
});

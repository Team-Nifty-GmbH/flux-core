<?php

use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Actions\StockPosting\CreateStockPostingsFromOrder;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\PriceList;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Warehouse;

require_once __DIR__ . '/Helpers.php';

test('only the chosen options post stock, the configurable parent never does', function (): void {
    $priceList = PriceList::factory()->create(['is_net' => true]);
    $order = configurationOrder($priceList);
    $warehouse = Warehouse::default();
    $server = configurationProduct(['is_configurable' => true, 'is_nos' => false]);
    configurationPrice($server, $priceList, '100');
    $group = configurationGroup();
    $ssd = configurationItem($group, configurationProduct(['is_nos' => false]), ['max_amount' => 2]);
    attachConfigurationGroup($server, $group);

    StockPosting::factory()->create([
        'warehouse_id' => $warehouse->getKey(),
        'product_id' => $ssd->product_id,
        'posting' => 10,
        'remaining_stock' => 10,
        'reserved_stock' => 0,
        'purchase_price' => 50,
    ]);

    $parent = CreateOrderPosition::make([
        'order_id' => $order->getKey(),
        'product_id' => $server->getKey(),
        'warehouse_id' => $warehouse->getKey(),
        'amount' => 2,
        'configuration' => [['product_configuration_group_item_id' => $ssd->getKey(), 'amount' => 2]],
    ])->validate()->execute();
    $child = OrderPosition::query()->where('parent_id', $parent->getKey())->sole();

    CreateStockPostingsFromOrder::make(['id' => $order->getKey()])->validate()->execute();

    expect(StockPosting::query()->where('order_position_id', $parent->getKey())->exists())->toBeFalse()
        ->and((float) StockPosting::query()->where('order_position_id', $child->getKey())->sum('posting'))->toEqual(-4.0);
});

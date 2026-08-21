<?php

use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Warehouse;
use FluxErp\Models\WarehouseBin;

test('a bin reports its stock and its available stock', function (): void {
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();
    $bin = WarehouseBin::factory()->create(['warehouse_id' => $warehouse->getKey()]);

    $layer = StockPosting::factory()->create([
        'warehouse_id' => $warehouse->getKey(),
        'product_id' => $product->getKey(),
        'warehouse_bin_id' => $bin->getKey(),
        'posting' => 10,
    ]);

    StockPosting::factory()->create([
        'warehouse_id' => $warehouse->getKey(),
        'product_id' => $product->getKey(),
        'warehouse_bin_id' => $bin->getKey(),
        'parent_id' => $layer->getKey(),
        'posting' => -4,
    ]);

    $layer->update(['remaining_stock' => 6]);

    $result = WarehouseBin::query()->withBinStock()->whereKey($bin->getKey())->first();

    expect(bccomp($result->stock, '6', 10))->toBe(0)
        ->and(bccomp($result->available_stock, '6', 10))->toBe(0);
});

test('an empty bin reports null stock', function (): void {
    $warehouse = Warehouse::factory()->create();
    $bin = WarehouseBin::factory()->create(['warehouse_id' => $warehouse->getKey()]);

    $result = WarehouseBin::query()->withBinStock()->whereKey($bin->getKey())->first();

    expect($result->stock)->toBeNull()
        ->and($result->available_stock)->toBeNull();
});

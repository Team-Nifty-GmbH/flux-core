<?php

use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Warehouse;
use FluxErp\Models\StorageArea;

test('a storage area reports its stock and its available stock', function (): void {
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();
    $storageArea = StorageArea::factory()->create(['warehouse_id' => $warehouse->getKey()]);

    $layer = StockPosting::factory()->create([
        'warehouse_id' => $warehouse->getKey(),
        'product_id' => $product->getKey(),
        'storage_area_id' => $storageArea->getKey(),
        'posting' => 10,
    ]);

    StockPosting::factory()->create([
        'warehouse_id' => $warehouse->getKey(),
        'product_id' => $product->getKey(),
        'storage_area_id' => $storageArea->getKey(),
        'parent_id' => $layer->getKey(),
        'posting' => -4,
    ]);

    $layer->update(['remaining_stock' => 6]);

    $reservedLayer = StockPosting::factory()->create([
        'warehouse_id' => $warehouse->getKey(),
        'product_id' => $product->getKey(),
        'storage_area_id' => $storageArea->getKey(),
        'posting' => 5,
    ]);

    $reservedLayer->update(['remaining_stock' => 0]);

    $result = StorageArea::query()->withStock()->whereKey($storageArea->getKey())->first();

    expect(bccomp($result->stock, '11', 10))->toBe(0)
        ->and(bccomp($result->available_stock, '6', 10))->toBe(0);
});

test('an empty storage area reports null stock', function (): void {
    $warehouse = Warehouse::factory()->create();
    $storageArea = StorageArea::factory()->create(['warehouse_id' => $warehouse->getKey()]);

    $result = StorageArea::query()->withStock()->whereKey($storageArea->getKey())->first();

    expect($result->stock)->toBeNull()
        ->and($result->available_stock)->toBeNull();
});

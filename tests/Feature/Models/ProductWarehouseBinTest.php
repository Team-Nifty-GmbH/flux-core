<?php

use FluxErp\Models\Product;
use FluxErp\Models\Warehouse;
use FluxErp\Models\WarehouseBin;

test('a product can have a fixed location in a bin', function (): void {
    $warehouse = Warehouse::factory()->create();
    $bin = WarehouseBin::factory()->create(['warehouse_id' => $warehouse->getKey()]);
    $product = Product::factory()->create();

    $product->warehouseBins()->attach($bin->getKey(), [
        'is_fixed_location' => true,
        'min_stock' => '10.0000000000',
        'max_stock' => '100.0000000000',
        'sort_order' => 5,
    ]);

    $attached = $product->fresh()->warehouseBins()->first();

    expect($attached->getKey())->toBe($bin->getKey())
        ->and((bool) $attached->pivot->is_fixed_location)->toBeTrue()
        ->and(bccomp($attached->pivot->min_stock, '10', 10))->toBe(0)
        ->and($attached->pivot->sort_order)->toBe(5);
});

test('a bin lists the products assigned to it', function (): void {
    $warehouse = Warehouse::factory()->create();
    $bin = WarehouseBin::factory()->create(['warehouse_id' => $warehouse->getKey()]);
    $product = Product::factory()->create();

    $bin->products()->attach($product->getKey());

    expect($bin->fresh()->products()->pluck('products.id')->all())->toBe([$product->getKey()]);
});

test('a product is assigned to a bin only once', function (): void {
    $warehouse = Warehouse::factory()->create();
    $bin = WarehouseBin::factory()->create(['warehouse_id' => $warehouse->getKey()]);
    $product = Product::factory()->create();

    $product->warehouseBins()->attach($bin->getKey());

    expect(fn () => $product->warehouseBins()->attach($bin->getKey()))
        ->toThrow(Illuminate\Database\QueryException::class);
});

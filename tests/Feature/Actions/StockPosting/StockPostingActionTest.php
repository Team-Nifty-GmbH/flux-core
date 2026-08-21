<?php

use FluxErp\Actions\StockPosting\CreateStockPosting;
use FluxErp\Actions\StockPosting\DeleteStockPosting;
use FluxErp\Models\Lot;
use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Warehouse;
use FluxErp\Models\WarehouseBin;

beforeEach(function (): void {
    $this->warehouse = Warehouse::factory()->create();
    $this->product = Product::factory()->create();
});

test('create stock posting', function (): void {
    $posting = CreateStockPosting::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'posting' => 10,
    ])->validate()->execute();

    expect($posting)->toBeInstanceOf(StockPosting::class);
});

test('create stock posting requires warehouse product and posting', function (): void {
    CreateStockPosting::assertValidationErrors([], ['warehouse_id', 'product_id', 'posting']);
});

test('delete stock posting', function (): void {
    $posting = StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
    ]);

    expect(DeleteStockPosting::make(['id' => $posting->getKey()])
        ->validate()->execute())->toBeTrue();
});

test('create stock posting round trips bin and lot', function (): void {
    $bin = WarehouseBin::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);
    $lot = Lot::factory()->create(['product_id' => $this->product->getKey()]);

    $posting = CreateStockPosting::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'warehouse_bin_id' => $bin->getKey(),
        'lot_id' => $lot->getKey(),
        'posting' => 10,
    ])->validate()->execute();

    expect($posting->warehouse_bin_id)->toBe($bin->getKey())
        ->and($posting->lot_id)->toBe($lot->getKey());
});

test('create stock posting rejects a non existent bin', function (): void {
    CreateStockPosting::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'warehouse_bin_id' => 999999,
        'posting' => 10,
    ], 'warehouse_bin_id');
});

test('create stock posting rejects a bin from a different warehouse', function (): void {
    $otherWarehouse = Warehouse::factory()->create();
    $bin = WarehouseBin::factory()->create(['warehouse_id' => $otherWarehouse->getKey()]);

    CreateStockPosting::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'warehouse_bin_id' => $bin->getKey(),
        'posting' => 10,
    ], 'warehouse_bin_id');
});

test('create stock posting rejects a lot from a different product', function (): void {
    $otherProduct = Product::factory()->create();
    $lot = Lot::factory()->create(['product_id' => $otherProduct->getKey()]);

    CreateStockPosting::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'lot_id' => $lot->getKey(),
        'posting' => 10,
    ], 'lot_id');
});

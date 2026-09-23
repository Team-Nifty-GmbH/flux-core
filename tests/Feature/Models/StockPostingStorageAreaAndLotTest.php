<?php

use FluxErp\Models\Lot;
use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\StorageArea;
use FluxErp\Models\Warehouse;

beforeEach(function (): void {
    $this->warehouse = Warehouse::factory()->create();
    $this->product = Product::factory()->create();
    $this->storageArea = StorageArea::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);
});

test('a stock posting carries its storage area and lot', function (): void {
    $lot = Lot::factory()->create(['product_id' => $this->product->getKey()]);

    $posting = StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $this->storageArea->getKey(),
        'lot_id' => $lot->getKey(),
        'posting' => 10,
    ]);

    expect($posting->storageArea->getKey())->toBe($this->storageArea->getKey())
        ->and($posting->lot->getKey())->toBe($lot->getKey());
});

test('legacy stock postings keep a null storage area and lot', function (): void {
    $posting = StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'posting' => 10,
    ]);

    expect($posting->storage_area_id)->toBeNull()
        ->and($posting->lot_id)->toBeNull()
        ->and($posting->storageArea)->toBeNull();
});

test('the warehouse level running balance ignores storage areas', function (): void {
    $other = StorageArea::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);

    StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $this->storageArea->getKey(),
        'posting' => 10,
    ]);
    $second = StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $other->getKey(),
        'posting' => 5,
    ]);

    expect(bccomp($second->stock, '15', 10))->toBe(0);
});

test('a storage area lists the stock postings that sit in it', function (): void {
    StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $this->storageArea->getKey(),
        'posting' => 10,
    ]);

    expect($this->storageArea->stockPostings()->count())->toBe(1);
});

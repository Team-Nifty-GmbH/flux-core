<?php

use FluxErp\Models\Lot;
use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Warehouse;
use FluxErp\Models\WarehouseBin;

beforeEach(function (): void {
    $this->warehouse = Warehouse::factory()->create();
    $this->product = Product::factory()->create();
    $this->bin = WarehouseBin::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);
});

test('a stock posting carries its bin and lot', function (): void {
    $lot = Lot::factory()->create(['product_id' => $this->product->getKey()]);

    $posting = StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'warehouse_bin_id' => $this->bin->getKey(),
        'lot_id' => $lot->getKey(),
        'posting' => 10,
    ]);

    expect($posting->warehouseBin->getKey())->toBe($this->bin->getKey())
        ->and($posting->lot->getKey())->toBe($lot->getKey());
});

test('legacy stock postings keep a null bin and lot', function (): void {
    $posting = StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'posting' => 10,
    ]);

    expect($posting->warehouse_bin_id)->toBeNull()
        ->and($posting->lot_id)->toBeNull()
        ->and($posting->warehouseBin)->toBeNull();
});

test('the warehouse level running balance ignores bins', function (): void {
    $other = WarehouseBin::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);

    StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'warehouse_bin_id' => $this->bin->getKey(),
        'posting' => 10,
    ]);
    $second = StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'warehouse_bin_id' => $other->getKey(),
        'posting' => 5,
    ]);

    expect(bccomp($second->stock, '15', 10))->toBe(0);
});

test('a bin lists the stock postings that sit in it', function (): void {
    StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'warehouse_bin_id' => $this->bin->getKey(),
        'posting' => 10,
    ]);

    expect($this->bin->stockPostings()->count())->toBe(1);
});

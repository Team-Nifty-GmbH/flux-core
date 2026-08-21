<?php

use FluxErp\Actions\StockPosting\CreateStockPosting;
use FluxErp\Models\Lot;
use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Warehouse;
use FluxErp\Models\WarehouseBin;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->warehouse = Warehouse::factory()->create();
    $this->product = Product::factory()->create();
    $this->bin = WarehouseBin::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);
});

test('a warehouse that requires bin locations rejects an incoming posting without one', function (): void {
    $this->warehouse->update(['requires_bin_location' => true]);

    CreateStockPosting::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'posting' => 5,
    ], 'warehouse_bin_id');
});

test('a warehouse that requires bin locations accepts an incoming posting with one', function (): void {
    $this->warehouse->update(['requires_bin_location' => true]);

    $posting = CreateStockPosting::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'warehouse_bin_id' => $this->bin->getKey(),
        'posting' => 5,
    ])->validate()->execute();

    expect($posting->warehouse_bin_id)->toBe($this->bin->getKey());
});

test('a lot tracked product rejects an incoming posting without a lot', function (): void {
    $this->product->update(['is_lot_tracked' => true]);

    CreateStockPosting::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'posting' => 5,
    ], 'lot_id');
});

test('a lot tracked product accepts an incoming posting with a lot', function (): void {
    $this->product->update(['is_lot_tracked' => true]);
    $lot = Lot::factory()->create(['product_id' => $this->product->getKey()]);

    $posting = CreateStockPosting::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'lot_id' => $lot->getKey(),
        'posting' => 5,
    ])->validate()->execute();

    expect($posting->lot_id)->toBe($lot->getKey());
});

test('a withdrawal may not exceed the remaining stock of its parent layer', function (): void {
    $layer = StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'posting' => 4,
    ]);

    CreateStockPosting::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'parent_id' => $layer->getKey(),
        'posting' => -9,
    ], 'posting');
});

test('a withdrawal against reserved stock is allowed', function (): void {
    $layer = StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'posting' => 10,
    ]);
    $layer->update(['remaining_stock' => 0, 'reserved_stock' => 10]);

    $withdrawal = CreateStockPosting::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'parent_id' => $layer->getKey(),
        'posting' => -10,
    ])->validate()->execute();

    expect(bccomp($withdrawal->posting, '-10', 10))->toBe(0);
});

test('an outgoing posting is exempt from the bin requirement', function (): void {
    $this->warehouse->update(['requires_bin_location' => true]);
    $nos = Product::factory()->create(['is_nos' => true]);

    $posting = CreateStockPosting::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $nos->getKey(),
        'posting' => -2,
    ])->validate()->execute();

    expect(bccomp($posting->posting, '-2', 10))->toBe(0);
});

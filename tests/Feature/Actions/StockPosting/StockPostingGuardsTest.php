<?php

use FluxErp\Actions\StockPosting\CreateStockPosting;
use FluxErp\Models\Lot;
use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\StorageArea;
use FluxErp\Models\Warehouse;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->warehouse = Warehouse::factory()->create();
    $this->product = Product::factory()->create();
    $this->storageArea = StorageArea::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);
});

test('a warehouse that requires storage areas rejects an incoming posting without one', function (): void {
    $this->warehouse->update(['requires_storage_area' => true]);

    CreateStockPosting::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'posting' => 5,
    ], 'storage_area_id');
});

test('a warehouse that requires storage areas accepts an incoming posting with one', function (): void {
    $this->warehouse->update(['requires_storage_area' => true]);

    $posting = CreateStockPosting::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $this->storageArea->getKey(),
        'posting' => 5,
    ])->validate()->execute();

    expect($posting->storage_area_id)->toBe($this->storageArea->getKey());
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

test('a withdrawal may draw the reserved stock of its parent layer', function (): void {
    $layer = StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'posting' => 10,
    ]);
    $layer->update(['remaining_stock' => 0, 'reserved_stock' => 10]);

    $child = CreateStockPosting::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'parent_id' => $layer->getKey(),
        'posting' => -10,
    ])
        ->validate()
        ->execute();

    $this->assertDatabaseHas('stock_postings', [
        'id' => $child->getKey(),
        'parent_id' => $layer->getKey(),
        'posting' => -10,
    ]);
});

test('a withdrawal may not exceed the remaining plus reserved stock of its parent layer', function (): void {
    $layer = StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'posting' => 10,
    ]);
    $layer->update(['remaining_stock' => 4, 'reserved_stock' => 6]);

    CreateStockPosting::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'parent_id' => $layer->getKey(),
        'posting' => -11,
    ], 'posting');
});

test('an outgoing posting is exempt from the storage area requirement', function (): void {
    $this->warehouse->update(['requires_storage_area' => true]);
    $nos = Product::factory()->create(['is_nos' => true]);

    $posting = CreateStockPosting::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $nos->getKey(),
        'posting' => -2,
    ])->validate()->execute();

    expect(bccomp($posting->posting, '-2', 10))->toBe(0);
});

test('an incoming posting into a storage area that is not a storage location is rejected', function (): void {
    $zone = StorageArea::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'is_storage_location' => false,
    ]);

    CreateStockPosting::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $zone->getKey(),
        'posting' => 5,
    ], 'storage_area_id');
});

test('an incoming posting into an inactive storage area is rejected', function (): void {
    $inactive = StorageArea::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'is_active' => false,
    ]);

    CreateStockPosting::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $inactive->getKey(),
        'posting' => 5,
    ], 'storage_area_id');
});

<?php

use FluxErp\Actions\StockPosting\CreateStockPosting;
use FluxErp\Actions\StockPosting\DeleteStockPosting;
use FluxErp\Actions\StockPosting\UpdateStockPosting;
use FluxErp\Models\Lot;
use FluxErp\Models\Product;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\StockPosting;
use FluxErp\Models\StorageArea;
use FluxErp\Models\Warehouse;

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

test('create stock posting round trips storage area and lot', function (): void {
    $storageArea = StorageArea::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);
    $lot = Lot::factory()->create(['product_id' => $this->product->getKey()]);

    $posting = CreateStockPosting::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $storageArea->getKey(),
        'lot_id' => $lot->getKey(),
        'posting' => 10,
    ])->validate()->execute();

    expect($posting->storage_area_id)->toBe($storageArea->getKey())
        ->and($posting->lot_id)->toBe($lot->getKey());
});

test('create stock posting rejects a non existent storage area', function (): void {
    CreateStockPosting::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => 999999,
        'posting' => 10,
    ], 'storage_area_id');
});

test('create stock posting rejects a storage area from a different warehouse', function (): void {
    $otherWarehouse = Warehouse::factory()->create();
    $storageArea = StorageArea::factory()->create(['warehouse_id' => $otherWarehouse->getKey()]);

    CreateStockPosting::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $storageArea->getKey(),
        'posting' => 10,
    ], 'storage_area_id');
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

test('update stock posting rejects a storage area from another warehouse', function (): void {
    $otherWarehouse = Warehouse::factory()->create();
    $storageArea = StorageArea::factory()->create(['warehouse_id' => $otherWarehouse->getKey()]);

    $posting = StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'posting' => 10,
    ]);

    UpdateStockPosting::assertValidationErrors([
        'id' => $posting->getKey(),
        'storage_area_id' => $storageArea->getKey(),
    ], 'storage_area_id');
});

test('update stock posting rejects a lot from another product', function (): void {
    $otherProduct = Product::factory()->create();
    $lot = Lot::factory()->create(['product_id' => $otherProduct->getKey()]);

    $posting = StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'posting' => 10,
    ]);

    UpdateStockPosting::assertValidationErrors([
        'id' => $posting->getKey(),
        'lot_id' => $lot->getKey(),
    ], 'lot_id');
});

test('update stock posting keeps a storage area and lot that stay within warehouse and product', function (): void {
    $storageArea = StorageArea::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);
    $lot = Lot::factory()->create(['product_id' => $this->product->getKey()]);

    $posting = StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'storage_area_id' => $storageArea->getKey(),
        'lot_id' => $lot->getKey(),
        'posting' => 10,
    ]);

    $updated = UpdateStockPosting::make([
        'id' => $posting->getKey(),
        'storage_area_id' => $storageArea->getKey(),
        'lot_id' => $lot->getKey(),
        'description' => 'Umgebucht',
    ])->validate()->execute();

    expect($updated->description)->toBe('Umgebucht')
        ->and($updated->storage_area_id)->toBe($storageArea->getKey())
        ->and($updated->lot_id)->toBe($lot->getKey());
});

test('create stock posting accepts a supplier serial number without a serial number', function (): void {
    $posting = CreateStockPosting::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'posting' => 1,
        'serial_number' => [
            'use_supplier_serial_number' => true,
            'supplier_serial_number' => 'SUP-4711',
        ],
    ])->validate()->execute();

    expect($posting->serial_number_id)->not->toBeNull()
        ->and(SerialNumber::query()->whereKey($posting->serial_number_id)->value('serial_number'))
        ->toBe('SUP-4711');
});

<?php

use FluxErp\Actions\StockPosting\TransferStock;
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
    $this->from = WarehouseBin::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);
    $this->to = WarehouseBin::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);
});

test('transferring moves stock as a posting pair and leaves the warehouse balance untouched', function (): void {
    $layer = StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'warehouse_bin_id' => $this->from->getKey(),
        'posting' => 10,
        'purchase_price' => 3,
    ]);

    TransferStock::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'from_warehouse_bin_id' => $this->from->getKey(),
        'to_warehouse_bin_id' => $this->to->getKey(),
        'amount' => 4,
    ])->validate()->execute();

    $outgoing = StockPosting::query()
        ->where('warehouse_bin_id', $this->from->getKey())
        ->where('posting', '<', 0)
        ->first();
    $incoming = StockPosting::query()
        ->where('warehouse_bin_id', $this->to->getKey())
        ->first();

    expect(bccomp($outgoing->posting, '-4', 10))->toBe(0)
        ->and($outgoing->parent_id)->toBe($layer->getKey())
        ->and(bccomp($incoming->posting, '4', 10))->toBe(0)
        ->and(bccomp($incoming->remaining_stock, '4', 10))->toBe(0)
        ->and(bccomp($incoming->purchase_price, '3', 10))->toBe(0)
        ->and(bccomp($layer->fresh()->remaining_stock, '6', 10))->toBe(0)
        ->and(bccomp((string) StockPosting::query()
            ->where('warehouse_id', $this->warehouse->getKey())
            ->where('product_id', $this->product->getKey())
            ->sum('posting'), '10', 10))->toBe(0);
});

test('a transfer inherits the lot of its source layer', function (): void {
    $lot = Lot::factory()->create(['product_id' => $this->product->getKey()]);
    StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'warehouse_bin_id' => $this->from->getKey(),
        'lot_id' => $lot->getKey(),
        'posting' => 5,
    ]);

    TransferStock::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'from_warehouse_bin_id' => $this->from->getKey(),
        'to_warehouse_bin_id' => $this->to->getKey(),
        'amount' => 5,
    ])->validate()->execute();

    expect(StockPosting::query()->where('warehouse_bin_id', $this->to->getKey())->first()->lot_id)
        ->toBe($lot->getKey());
});

test('a transfer spanning several layers creates one pair per layer', function (): void {
    StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'warehouse_bin_id' => $this->from->getKey(),
        'posting' => 3,
    ]);
    StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'warehouse_bin_id' => $this->from->getKey(),
        'posting' => 3,
    ]);

    TransferStock::make([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'from_warehouse_bin_id' => $this->from->getKey(),
        'to_warehouse_bin_id' => $this->to->getKey(),
        'amount' => 5,
    ])->validate()->execute();

    expect(StockPosting::query()->where('warehouse_bin_id', $this->to->getKey())->count())->toBe(2);
});

test('transferring more than the source bin holds is rejected', function (): void {
    StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'warehouse_bin_id' => $this->from->getKey(),
        'posting' => 2,
    ]);

    TransferStock::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'from_warehouse_bin_id' => $this->from->getKey(),
        'to_warehouse_bin_id' => $this->to->getKey(),
        'amount' => 9,
    ], 'amount');
});

test('a target bin from another warehouse is rejected', function (): void {
    $other = Warehouse::factory()->create();
    $foreign = WarehouseBin::factory()->create(['warehouse_id' => $other->getKey()]);

    TransferStock::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'from_warehouse_bin_id' => $this->from->getKey(),
        'to_warehouse_bin_id' => $foreign->getKey(),
        'amount' => 1,
    ], 'to_warehouse_bin_id');
});

test('a target bin that is not a storage location is rejected', function (): void {
    $zone = WarehouseBin::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'is_storage_location' => false,
    ]);

    TransferStock::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'from_warehouse_bin_id' => $this->from->getKey(),
        'to_warehouse_bin_id' => $zone->getKey(),
        'amount' => 1,
    ], 'to_warehouse_bin_id');
});

test('source and target bin may not be the same', function (): void {
    TransferStock::assertValidationErrors([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'from_warehouse_bin_id' => $this->from->getKey(),
        'to_warehouse_bin_id' => $this->from->getKey(),
        'amount' => 1,
    ], 'to_warehouse_bin_id');
});

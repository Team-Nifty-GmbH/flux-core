<?php

use FluxErp\Enums\StockRemovalStrategyEnum;
use FluxErp\Models\Lot;
use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Warehouse;
use FluxErp\Models\WarehouseBin;
use FluxErp\Support\Stock\StockAllocator;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->warehouse = Warehouse::factory()->create();
    $this->product = Product::factory()->create();

    $this->layer = function (string|int|float $posting, array $attributes = []): StockPosting {
        return StockPosting::factory()->create(array_merge([
            'warehouse_id' => $this->warehouse->getKey(),
            'product_id' => $this->product->getKey(),
            'posting' => $posting,
        ], $attributes));
    };

    $this->allocator = fn (): StockAllocator => app(StockAllocator::class)
        ->forProduct($this->product->getKey())
        ->inWarehouse($this->warehouse->getKey());
});

test('fifo takes the oldest layer first', function (): void {
    $first = ($this->layer)(10);
    $second = ($this->layer)(10);

    $allocation = ($this->allocator)()->withStrategy(StockRemovalStrategyEnum::Fifo)->allocate(15);

    expect($allocation)->toHaveCount(2)
        ->and($allocation[0]['stockPosting']->getKey())->toBe($first->getKey())
        ->and(bccomp($allocation[0]['amount'], '10', 10))->toBe(0)
        ->and($allocation[1]['stockPosting']->getKey())->toBe($second->getKey())
        ->and(bccomp($allocation[1]['amount'], '5', 10))->toBe(0);
});

test('lifo takes the newest layer first', function (): void {
    ($this->layer)(10);
    $second = ($this->layer)(10);

    $allocation = ($this->allocator)()->withStrategy(StockRemovalStrategyEnum::Lifo)->allocate(4);

    expect($allocation)->toHaveCount(1)
        ->and($allocation[0]['stockPosting']->getKey())->toBe($second->getKey());
});

test('fefo takes the shortest shelf life first and puts lotless layers last', function (): void {
    $lotless = ($this->layer)(10);

    $late = Lot::factory()->create([
        'product_id' => $this->product->getKey(),
        'expires_at' => '2027-12-31',
    ]);
    $early = Lot::factory()->create([
        'product_id' => $this->product->getKey(),
        'expires_at' => '2027-01-31',
    ]);

    $lateLayer = ($this->layer)(10, ['lot_id' => $late->getKey()]);
    $earlyLayer = ($this->layer)(10, ['lot_id' => $early->getKey()]);

    $allocation = ($this->allocator)()->withStrategy(StockRemovalStrategyEnum::Fefo)->allocate(25);

    expect($allocation->pluck('stockPosting.id')->all())
        ->toBe([$earlyLayer->getKey(), $lateLayer->getKey(), $lotless->getKey()]);
});

test('a blocked lot is excluded', function (): void {
    $blocked = Lot::factory()->create([
        'product_id' => $this->product->getKey(),
        'blocked_at' => '2026-08-21 10:00:00',
    ]);
    ($this->layer)(10, ['lot_id' => $blocked->getKey()]);
    $usable = ($this->layer)(10);

    $allocation = ($this->allocator)()->allocate(20);

    expect($allocation)->toHaveCount(1)
        ->and($allocation[0]['stockPosting']->getKey())->toBe($usable->getKey())
        ->and(bccomp($allocation[0]['amount'], '10', 10))->toBe(0);
});

test('an inactive bin is excluded', function (): void {
    $inactive = WarehouseBin::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'is_active' => false,
    ]);
    ($this->layer)(10, ['warehouse_bin_id' => $inactive->getKey()]);
    $usable = ($this->layer)(10);

    $allocation = ($this->allocator)()->allocate(20);

    expect($allocation)->toHaveCount(1)
        ->and($allocation[0]['stockPosting']->getKey())->toBe($usable->getKey());
});

test('the bin scope restricts the allocation to the given bins', function (): void {
    $wanted = WarehouseBin::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);
    $other = WarehouseBin::factory()->create(['warehouse_id' => $this->warehouse->getKey()]);

    $inWanted = ($this->layer)(10, ['warehouse_bin_id' => $wanted->getKey()]);
    ($this->layer)(10, ['warehouse_bin_id' => $other->getKey()]);

    $allocation = ($this->allocator)()->inBins([$wanted->getKey()])->allocate(20);

    expect($allocation)->toHaveCount(1)
        ->and($allocation[0]['stockPosting']->getKey())->toBe($inWanted->getKey());
});

test('allocating more than available returns only what exists', function (): void {
    ($this->layer)(3);

    $allocation = ($this->allocator)()->allocate(99);

    expect($allocation)->toHaveCount(1)
        ->and(bccomp($allocation[0]['amount'], '3', 10))->toBe(0);
});

test('the product strategy beats the warehouse strategy', function (): void {
    $this->warehouse->update(['stock_removal_strategy_enum' => StockRemovalStrategyEnum::Fifo]);
    $this->product->update(['stock_removal_strategy_enum' => StockRemovalStrategyEnum::Lifo]);

    ($this->layer)(10);
    $second = ($this->layer)(10);

    $allocation = ($this->allocator)()->allocate(1);

    expect($allocation[0]['stockPosting']->getKey())->toBe($second->getKey());
});

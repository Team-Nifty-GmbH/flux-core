<?php

use FluxErp\Models\Lot;
use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Warehouse;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->warehouse = Warehouse::factory()->create();
    $this->product = Product::factory()->create();

    $this->layerWithLot = function (?string $expiresAt, string|int|float $posting = 10): StockPosting {
        $lot = Lot::factory()->create([
            'product_id' => $this->product->getKey(),
            'expires_at' => $expiresAt,
        ]);

        return StockPosting::factory()->create([
            'warehouse_id' => $this->warehouse->getKey(),
            'product_id' => $this->product->getKey(),
            'lot_id' => $lot->getKey(),
            'posting' => $posting,
        ]);
    };
});

test('expiring within finds layers whose lot expires inside the window', function (): void {
    $soon = ($this->layerWithLot)(now()->addDays(10)->toDateString());
    ($this->layerWithLot)(now()->addDays(90)->toDateString());

    expect(StockPosting::query()->expiringWithin(30)->pluck('id')->all())
        ->toBe([$soon->getKey()]);
});

test('expiring within ignores layers without a lot', function (): void {
    StockPosting::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'posting' => 10,
    ]);

    expect(StockPosting::query()->expiringWithin(30)->count())->toBe(0);
});

test('expiring within ignores lots without a best before date', function (): void {
    ($this->layerWithLot)(null);

    expect(StockPosting::query()->expiringWithin(30)->count())->toBe(0);
});

test('expiring within ignores exhausted layers', function (): void {
    $layer = ($this->layerWithLot)(now()->addDays(5)->toDateString());
    $layer->update(['remaining_stock' => 0]);

    expect(StockPosting::query()->expiringWithin(30)->count())->toBe(0);
});

test('expiring within keeps layers whose lot already expired', function (): void {
    $expired = ($this->layerWithLot)(now()->subDays(10)->toDateString());
    $soon = ($this->layerWithLot)(now()->addDays(10)->toDateString());
    ($this->layerWithLot)(now()->addDays(90)->toDateString());

    expect(StockPosting::query()->expiringWithin(30)->pluck('id')->all())
        ->toEqualCanonicalizing([$expired->getKey(), $soon->getKey()]);
});

test('expiring within refuses a window shorter than a day', function (): void {
    expect(fn () => StockPosting::query()->expiringWithin(0)->get())
        ->toThrow(InvalidArgumentException::class)
        ->and(fn () => StockPosting::query()->expiringWithin(-5)->get())
        ->toThrow(InvalidArgumentException::class);
});

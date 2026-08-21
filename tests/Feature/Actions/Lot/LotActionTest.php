<?php

use FluxErp\Actions\Lot\CreateLot;
use FluxErp\Actions\Lot\DeleteLot;
use FluxErp\Actions\Lot\UpdateLot;
use FluxErp\Models\Lot;
use FluxErp\Models\Product;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Warehouse;

beforeEach(function (): void {
    $this->product = Product::factory()->create();
});

test('create lot', function (): void {
    $lot = CreateLot::make([
        'product_id' => $this->product->getKey(),
        'lot_number' => 'CH-2026-08',
        'expires_at' => '2027-06-30',
    ])->validate()->execute();

    expect($lot)->toBeInstanceOf(Lot::class)
        ->lot_number->toBe('CH-2026-08')
        ->and($lot->expires_at->toDateString())->toBe('2027-06-30');
});

test('create lot requires product and lot number', function (): void {
    CreateLot::assertValidationErrors([], 'product_id');
    CreateLot::assertValidationErrors(['product_id' => $this->product->getKey()], 'lot_number');
});

test('create lot rejects a duplicate lot number for the same product', function (): void {
    Lot::factory()->create(['product_id' => $this->product->getKey(), 'lot_number' => 'CH-2026-08']);

    CreateLot::assertValidationErrors([
        'product_id' => $this->product->getKey(),
        'lot_number' => 'CH-2026-08',
    ], 'lot_number');
});

test('update lot blocks it', function (): void {
    $lot = Lot::factory()->create(['product_id' => $this->product->getKey()]);

    $updated = UpdateLot::make([
        'id' => $lot->getKey(),
        'blocked_at' => '2026-08-21 12:00:00',
    ])->validate()->execute();

    expect($updated->blocked_at)->not->toBeNull();
});

test('update lot rejects a duplicate lot number for the same product', function (): void {
    Lot::factory()->create(['product_id' => $this->product->getKey(), 'lot_number' => 'CH-2026-08']);
    $lot = Lot::factory()->create(['product_id' => $this->product->getKey(), 'lot_number' => 'CH-2026-09']);

    UpdateLot::assertValidationErrors([
        'id' => $lot->getKey(),
        'lot_number' => 'CH-2026-08',
    ], 'lot_number');
});

test('update lot allows re-saving its own unchanged lot number', function (): void {
    $lot = Lot::factory()->create(['product_id' => $this->product->getKey(), 'lot_number' => 'CH-2026-08']);

    $updated = UpdateLot::make(['id' => $lot->getKey(), 'lot_number' => 'CH-2026-08'])
        ->validate()->execute();

    expect($updated->lot_number)->toBe('CH-2026-08');
});

test('delete lot', function (): void {
    $lot = Lot::factory()->create(['product_id' => $this->product->getKey()]);

    expect(DeleteLot::make(['id' => $lot->getKey()])->validate()->execute())->toBeTrue();
});

test('delete lot refuses while stock postings reference it', function (): void {
    $warehouse = Warehouse::factory()->create();
    $lot = Lot::factory()->create(['product_id' => $this->product->getKey()]);

    StockPosting::factory()->create([
        'warehouse_id' => $warehouse->getKey(),
        'product_id' => $this->product->getKey(),
        'lot_id' => $lot->getKey(),
        'posting' => 10,
    ]);

    DeleteLot::assertValidationErrors(['id' => $lot->getKey()], 'stock_postings');
});

test('create lot rejects a lot number held by a trashed lot', function (): void {
    Lot::factory()
        ->create(['product_id' => $this->product->getKey(), 'lot_number' => 'CH-2026-08'])
        ->delete();

    CreateLot::assertValidationErrors([
        'product_id' => $this->product->getKey(),
        'lot_number' => 'CH-2026-08',
    ], 'lot_number');
});

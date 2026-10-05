<?php

use FluxErp\Models\Lot;
use FluxErp\Models\Product;

test('lot belongs to a product', function (): void {
    $product = Product::factory()->create();
    $lot = Lot::factory()->create(['product_id' => $product->getKey()]);

    expect($lot->product->getKey())->toBe($product->getKey());
});

test('lot casts its dates', function (): void {
    $product = Product::factory()->create();
    $lot = Lot::factory()->create([
        'product_id' => $product->getKey(),
        'expires_at' => '2027-01-31',
        'blocked_at' => '2026-08-21 10:00:00',
    ])->fresh();

    expect($lot->expires_at)->toBeInstanceOf(Carbon\CarbonInterface::class)
        ->and($lot->expires_at->toDateString())->toBe('2027-01-31')
        ->and($lot->blocked_at)->toBeInstanceOf(Carbon\CarbonInterface::class);
});

test('lot number is unique per product only', function (): void {
    $first = Product::factory()->create();
    $second = Product::factory()->create();

    Lot::factory()->create(['product_id' => $first->getKey(), 'lot_number' => 'L-1']);
    $other = Lot::factory()->create(['product_id' => $second->getKey(), 'lot_number' => 'L-1']);

    expect($other->exists)->toBeTrue();
});

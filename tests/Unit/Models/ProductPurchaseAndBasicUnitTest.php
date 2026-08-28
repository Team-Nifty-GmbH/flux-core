<?php

use FluxErp\Models\Product;
use FluxErp\Models\Unit;

test('price per basic unit follows the documented formula', function (): void {
    $product = Product::factory()->make([
        'selling_unit' => 750,
        'basic_unit' => 1000,
    ]);

    expect($product->pricePerBasicUnit(9))->toEqual(12);
});

test('price per basic unit is null when selling unit and basic unit match', function (): void {
    $product = Product::factory()->make([
        'selling_unit' => 1000,
        'basic_unit' => 1000,
    ]);

    expect($product->pricePerBasicUnit(9))->toBeNull();
});

test('price per basic unit is null without both units', function (): void {
    $product = Product::factory()->make([
        'selling_unit' => null,
        'basic_unit' => 1000,
    ]);

    expect($product->pricePerBasicUnit(9))->toBeNull();
});

test('price per basic unit is null for a stored zero unit', function (): void {
    $product = Product::factory()->create([
        'selling_unit' => 0,
        'basic_unit' => 1000,
    ])->refresh();

    expect($product->selling_unit)->toBeString()
        ->and($product->pricePerBasicUnit(9))->toBeNull();
});

test('purchase unit and reference unit are related', function (): void {
    $purchaseUnit = Unit::factory()->create();
    $referenceUnit = Unit::factory()->create();

    $product = Product::factory()->create([
        'purchase_unit_id' => $purchaseUnit->getKey(),
        'reference_unit_id' => $referenceUnit->getKey(),
    ]);

    expect($product->purchaseUnit->getKey())->toBe($purchaseUnit->getKey())
        ->and($product->referenceUnit->getKey())->toBe($referenceUnit->getKey());
});

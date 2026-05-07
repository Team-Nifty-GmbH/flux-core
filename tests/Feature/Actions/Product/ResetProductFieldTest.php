<?php

use FluxErp\Actions\Product\ResetProductField;
use FluxErp\Models\Product;
use Illuminate\Validation\ValidationException;

it('removes a field from variant overridden_fields', function (): void {
    $parent = Product::factory()->create(['name' => 'Parent']);
    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['name'],
        'name' => 'override',
    ]);

    ResetProductField::make([
        'id' => $variant->getKey(),
        'field' => 'name',
    ])->validate()->execute();

    expect($variant->fresh()->overridden_fields)->toBeNull();
});

it('rejects non-inheritable fields', function (): void {
    $parent = Product::factory()->create();
    $variant = Product::factory()->create(['parent_id' => $parent->getKey()]);

    ResetProductField::make([
        'id' => $variant->getKey(),
        'field' => 'product_number',
    ])->validate()->execute();
})->throws(ValidationException::class);

it('rejects when id refers to a non-variant', function (): void {
    $product = Product::factory()->create(['parent_id' => null]);

    ResetProductField::make([
        'id' => $product->getKey(),
        'field' => 'name',
    ])->validate()->execute();
})->throws(ValidationException::class);

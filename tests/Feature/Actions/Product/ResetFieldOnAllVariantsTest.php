<?php

use FluxErp\Actions\Product\ResetFieldOnAllVariants;
use FluxErp\Models\Product;
use Illuminate\Validation\ValidationException;

it('removes the field from every variants overridden_fields', function (): void {
    $parent = Product::factory()->create(['name' => 'Parent']);
    Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['name'],
    ]);
    Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['name', 'description'],
    ]);

    $touched = ResetFieldOnAllVariants::make([
        'parent_id' => $parent->getKey(),
        'field' => 'name',
    ])->validate()->execute();

    expect($touched)->toBe(2);
});

it('rejects when parent_id refers to a variant', function (): void {
    $top = Product::factory()->create();
    $variant = Product::factory()->create(['parent_id' => $top->getKey()]);

    ResetFieldOnAllVariants::make([
        'parent_id' => $variant->getKey(),
        'field' => 'name',
    ])->validate()->execute();
})->throws(ValidationException::class);

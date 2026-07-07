<?php

use FluxErp\Actions\Product\ResetProductField;
use FluxErp\Models\Product;
use Illuminate\Validation\ValidationException;

it('removes a field from variant overridden_fields', function (): void {
    $parent = Product::factory()->create(['name' => 'Parent']);
    // Match every other inheritable field to the parent so the saving hook (value-diff
    // based overrides) only marks 'name' — Product::factory() otherwise randomizes fields
    // like description/seo_keywords independently for parent and variant.
    $matchingParent = collect($parent->getInheritableFields())
        ->mapWithKeys(fn (string $field): array => [$field => $parent->{$field}])
        ->reject(fn (mixed $value): bool => is_null($value))
        ->all();
    $variant = Product::factory()->create(array_merge($matchingParent, [
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['name'],
        'name' => 'override',
    ]));

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

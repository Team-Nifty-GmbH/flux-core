<?php

use FluxErp\Actions\Product\ResetFieldOnAllVariants;
use FluxErp\Models\Product;
use FluxErp\Settings\ProductSettings;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
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

it('re-copies the parents current value into every affected variants column', function (): void {
    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => true])->save();

    $parent = Product::factory()->create(['weight_gram' => 100]);
    $matchingParent = collect($parent->getInheritableFields())
        ->mapWithKeys(fn (string $field): array => [$field => $parent->{$field}])
        ->reject(fn (mixed $value): bool => is_null($value))
        ->all();
    $overridden = Product::factory()->create(array_merge($matchingParent, [
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['weight_gram'],
        'weight_gram' => 999,
    ]));
    $nonOverridden = Product::factory()->create(array_merge($matchingParent, [
        'parent_id' => $parent->getKey(),
        'weight_gram' => 100,
    ]));

    $parent->update(['weight_gram' => 250]);

    $touched = ResetFieldOnAllVariants::make([
        'parent_id' => $parent->getKey(),
        'field' => 'weight_gram',
    ])->validate()->execute();

    expect($touched)->toBe(1);
    expect($overridden->fresh()->overridden_fields)->toBeNull();
    expect((int) DB::table('products')->where('id', $overridden->getKey())->value('weight_gram'))
        ->toBe(250);
    expect((int) DB::table('products')->where('id', $nonOverridden->getKey())->value('weight_gram'))
        ->toBe(250);
});

it('throws when resetting a field on a non-existent parent', function (): void {
    // Bypasses ->validate() on purpose: this exercises the firstOrFail() guard in
    // performAction() itself, not the ModelExists validation rule.
    ResetFieldOnAllVariants::make([
        'parent_id' => 999999999,
        'field' => 'name',
    ])->execute();
})->throws(ModelNotFoundException::class);

it('rejects when parent_id refers to a variant', function (): void {
    $top = Product::factory()->create();
    $variant = Product::factory()->create(['parent_id' => $top->getKey()]);

    ResetFieldOnAllVariants::make([
        'parent_id' => $variant->getKey(),
        'field' => 'name',
    ])->validate()->execute();
})->throws(ValidationException::class);

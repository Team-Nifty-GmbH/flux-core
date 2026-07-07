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

it('re-copies the parents current translation into every affected variants attribute_translations', function (): void {
    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => true])->save();

    $parent = Product::factory()->create(['name' => 'Parent Name']);
    $matchingParent = collect($parent->getInheritableFields())
        ->mapWithKeys(fn (string $field): array => [$field => $parent->{$field}])
        ->reject(fn (mixed $value): bool => is_null($value))
        ->all();
    $overridden = Product::factory()->create(array_merge($matchingParent, [
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['name'],
        'name' => 'Variant Override Name',
    ]));
    $nonOverridden = Product::factory()->create(array_merge($matchingParent, [
        'parent_id' => $parent->getKey(),
        'name' => 'Parent Name',
    ]));

    $language = FluxErp\Models\Language::factory()->create(['language_code' => 'fr']);
    DB::table('attribute_translations')->insert([
        'model_type' => $parent->getMorphClass(),
        'model_id' => $parent->getKey(),
        'attribute' => 'name',
        'language_id' => $language->getKey(),
        'value' => 'Nom Parent',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $touched = ResetFieldOnAllVariants::make([
        'parent_id' => $parent->getKey(),
        'field' => 'name',
    ])->validate()->execute();

    expect($touched)->toBe(1);

    $fetch = fn (Product $model) => DB::table('attribute_translations')
        ->where('model_type', $model->getMorphClass())
        ->where('model_id', $model->getKey())
        ->where('attribute', 'name')
        ->where('language_id', $language->getKey())
        ->whereNull('deleted_at')
        ->value('value');

    expect($fetch($overridden))->toBe('Nom Parent')
        ->and($fetch($nonOverridden))->toBe('Nom Parent');
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

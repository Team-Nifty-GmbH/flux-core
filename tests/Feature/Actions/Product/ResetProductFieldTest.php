<?php

use FluxErp\Actions\Product\ResetProductField;
use FluxErp\Models\Product;
use FluxErp\Settings\ProductSettings;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
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

it('re-copies the parents current value into the variants column on reset', function (): void {
    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => true])->save();

    $parent = Product::factory()->create(['weight_gram' => 100]);
    $matchingParent = collect($parent->getInheritableFields())
        ->mapWithKeys(fn (string $field): array => [$field => $parent->{$field}])
        ->reject(fn (mixed $value): bool => is_null($value))
        ->all();
    $variant = Product::factory()->create(array_merge($matchingParent, [
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['weight_gram'],
        'weight_gram' => 999,
    ]));

    // Parent changes after the variant already diverged — the reset must pick up
    // this current value, not whatever the parent held when the override was set.
    $parent->update(['weight_gram' => 250]);

    ResetProductField::make([
        'id' => $variant->getKey(),
        'field' => 'weight_gram',
    ])->validate()->execute();

    $variant = $variant->fresh();
    expect($variant->overridden_fields)->toBeNull();
    expect((int) DB::table('products')->where('id', $variant->getKey())->value('weight_gram'))
        ->toBe(250);
});

it('re-copies the parents current translation onto the variant on reset', function (): void {
    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => true])->save();

    $parent = Product::factory()->create(['name' => 'Parent Name']);
    $matchingParent = collect($parent->getInheritableFields())
        ->mapWithKeys(fn (string $field): array => [$field => $parent->{$field}])
        ->reject(fn (mixed $value): bool => is_null($value))
        ->all();
    $variant = Product::factory()->create(array_merge($matchingParent, [
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['name'],
        'name' => 'Variant Override Name',
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

    ResetProductField::make([
        'id' => $variant->getKey(),
        'field' => 'name',
    ])->validate()->execute();

    expect(
        DB::table('attribute_translations')
            ->where('model_type', $variant->getMorphClass())
            ->where('model_id', $variant->getKey())
            ->where('attribute', 'name')
            ->where('language_id', $language->getKey())
            ->whereNull('deleted_at')
            ->value('value')
    )->toBe('Nom Parent');
});

it('scopes the resync update to the reset variant, not its non-overriding siblings', function (): void {
    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => true])->save();

    $parent = Product::factory()->create(['weight_gram' => 100]);
    $matchingParent = collect($parent->getInheritableFields())
        ->mapWithKeys(fn (string $field): array => [$field => $parent->{$field}])
        ->reject(fn (mixed $value): bool => is_null($value))
        ->all();
    $variant = Product::factory()->create(array_merge($matchingParent, [
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['weight_gram'],
        'weight_gram' => 999,
    ]));
    $sibling1 = Product::factory()->create(array_merge($matchingParent, [
        'parent_id' => $parent->getKey(),
        'weight_gram' => 100,
    ]));
    $sibling2 = Product::factory()->create(array_merge($matchingParent, [
        'parent_id' => $parent->getKey(),
        'weight_gram' => 100,
    ]));

    $parent->update(['weight_gram' => 250]);

    DB::enableQueryLog();
    ResetProductField::make([
        'id' => $variant->getKey(),
        'field' => 'weight_gram',
    ])->validate()->execute();
    // Isolate the SyncVariantInheritanceJob's own resync UPDATE (it's the only `products`
    // UPDATE keyed by `parent_id` — the variant's own save() updates by `id` instead).
    $syncUpdate = collect(DB::getQueryLog())
        ->first(fn (array $entry): bool => str_contains($entry['query'], 'update `products`')
            && str_contains($entry['query'], '`parent_id` ='));
    DB::disableQueryLog();

    expect($syncUpdate)->not->toBeNull();
    expect($syncUpdate['bindings'])->toContain($variant->getKey())
        ->and($syncUpdate['bindings'])->not->toContain($sibling1->getKey())
        ->and($syncUpdate['bindings'])->not->toContain($sibling2->getKey());
});

it('throws when resetting a field on a non-existent product', function (): void {
    // Bypasses ->validate() on purpose: this exercises the firstOrFail() guard in
    // performAction() itself, not the ModelExists validation rule.
    ResetProductField::make([
        'id' => 999999999,
        'field' => 'name',
    ])->execute();
})->throws(ModelNotFoundException::class);

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

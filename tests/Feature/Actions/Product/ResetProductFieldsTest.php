<?php

use FluxErp\Actions\Product\ResetProductFields;
use FluxErp\Models\Language;
use FluxErp\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

test('removes the fields from every variants overridden_fields', function (): void {
    $parent = Product::factory()->create(['name' => 'Parent']);
    Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['name'],
    ]);
    Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['name', 'description'],
    ]);

    $touched = ResetProductFields::make([
        'parent_id' => $parent->getKey(),
        'fields' => ['name'],
    ])
        ->validate()
        ->execute();

    expect($touched)->toBe(2);
});

test('resets multiple fields in one call', function (): void {
    $parent = Product::factory()->create(['name' => 'Parent']);
    $matchingParent = collect($parent->getInheritableFields())
        ->mapWithKeys(fn (string $field): array => [$field => $parent->{$field}])
        ->reject(fn (mixed $value): bool => is_null($value))
        ->all();
    $variant = Product::factory()->create(array_merge($matchingParent, [
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['name', 'description'],
        'name' => 'Override Name',
        'description' => 'Override Description',
    ]));

    $touched = ResetProductFields::make([
        'parent_id' => $parent->getKey(),
        'fields' => ['name', 'description'],
    ])
        ->validate()
        ->execute();

    expect($touched)->toBe(1)
        ->and($variant->fresh()->overridden_fields)->toBeNull();
});

test('resets a single variant when variant_ids are given', function (): void {
    $parent = Product::factory()->create(['name' => 'Parent']);
    $matchingParent = collect($parent->getInheritableFields())
        ->mapWithKeys(fn (string $field): array => [$field => $parent->{$field}])
        ->reject(fn (mixed $value): bool => is_null($value))
        ->all();
    $variant = Product::factory()->create(array_merge($matchingParent, [
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['name'],
        'name' => 'Override Name',
    ]));
    $sibling = Product::factory()->create(array_merge($matchingParent, [
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['name'],
        'name' => 'Sibling Override Name',
    ]));

    $touched = ResetProductFields::make([
        'parent_id' => $parent->getKey(),
        'fields' => ['name'],
        'variant_ids' => [$variant->getKey()],
    ])
        ->validate()
        ->execute();

    expect($touched)->toBe(1)
        ->and($variant->fresh()->overridden_fields)->toBeNull()
        ->and($sibling->fresh()->overridden_fields)->toBe(['name']);
});

test('re-copies the parents current value into every affected variants column', function (): void {
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

    $touched = ResetProductFields::make([
        'parent_id' => $parent->getKey(),
        'fields' => ['weight_gram'],
    ])
        ->validate()
        ->execute();

    expect($touched)->toBe(1)
        ->and($overridden->fresh()->overridden_fields)->toBeNull()
        ->and((int) DB::table('products')->where('id', $overridden->getKey())->value('weight_gram'))
        ->toBe(250)
        ->and((int) DB::table('products')->where('id', $nonOverridden->getKey())->value('weight_gram'))
        ->toBe(250);
});

test('re-copies the parents current translation onto the reset variants', function (): void {
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

    $language = Language::factory()->create(['language_code' => 'fr']);
    DB::table('attribute_translations')->insert([
        'model_type' => $parent->getMorphClass(),
        'model_id' => $parent->getKey(),
        'attribute' => 'name',
        'language_id' => $language->getKey(),
        'value' => 'Nom Parent',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    ResetProductFields::make([
        'parent_id' => $parent->getKey(),
        'fields' => ['name'],
    ])
        ->validate()
        ->execute();

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

test('scopes the resync update to the given variant, not its non-overriding siblings', function (): void {
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
    $sibling = Product::factory()->create(array_merge($matchingParent, [
        'parent_id' => $parent->getKey(),
        'weight_gram' => 100,
    ]));

    $parent->update(['weight_gram' => 250]);

    DB::enableQueryLog();
    ResetProductFields::make([
        'parent_id' => $parent->getKey(),
        'fields' => ['weight_gram'],
        'variant_ids' => [$variant->getKey()],
    ])
        ->validate()
        ->execute();
    // Isolate the sync's own resync UPDATE (it's the only `products` UPDATE keyed
    // by `parent_id`). whereIntegerInRaw() inlines the ids into the SQL string.
    $syncUpdate = collect(DB::getQueryLog())
        ->first(fn (array $entry): bool => str_contains($entry['query'], 'update `products`')
            && str_contains($entry['query'], '`parent_id` ='));
    DB::disableQueryLog();

    expect($syncUpdate)->not->toBeNull()
        ->and($syncUpdate['query'])->toContain('in (' . $variant->getKey() . ')')
        ->and($syncUpdate['query'])->not->toContain('in (' . $sibling->getKey() . ')');
});

test('throws when resetting fields on a non-existent parent', function (): void {
    // Bypasses ->validate() on purpose: this exercises the firstOrFail() guard in
    // performAction() itself, not the ModelExists validation rule.
    ResetProductFields::make([
        'parent_id' => 999999999,
        'fields' => ['name'],
    ])->execute();
})->throws(ModelNotFoundException::class);

test('rejects non-inheritable fields', function (): void {
    $parent = Product::factory()->create();
    Product::factory()->create(['parent_id' => $parent->getKey()]);

    ResetProductFields::make([
        'parent_id' => $parent->getKey(),
        'fields' => ['product_number'],
    ])
        ->validate()
        ->execute();
})->throws(ValidationException::class);

test('rejects when parent_id refers to a variant', function (): void {
    $top = Product::factory()->create();
    $variant = Product::factory()->create(['parent_id' => $top->getKey()]);

    ResetProductFields::make([
        'parent_id' => $variant->getKey(),
        'fields' => ['name'],
    ])
        ->validate()
        ->execute();
})->throws(ValidationException::class);

test('rejects variant ids belonging to another parent', function (): void {
    $parent = Product::factory()->create(['name' => 'Parent']);
    $otherParent = Product::factory()->create(['name' => 'Other Parent']);
    $foreignVariant = Product::factory()->create([
        'parent_id' => $otherParent->getKey(),
        'overridden_fields' => ['name'],
    ]);
    // The model recomputes overridden_fields on save, so capture what was persisted.
    $overriddenBefore = $foreignVariant->fresh()->overridden_fields;

    try {
        ResetProductFields::make([
            'parent_id' => $parent->getKey(),
            'fields' => ['name'],
            'variant_ids' => [$foreignVariant->getKey()],
        ])
            ->validate()
            ->execute();

        $this->fail('Expected the foreign variant to be rejected.');
    } catch (ValidationException $e) {
        expect($e->errors())->toHaveKey('variant_ids');
    }

    // The foreign variant must keep its overrides instead of being silently ignored.
    expect($foreignVariant->fresh()->overridden_fields)->toBe($overriddenBefore);
});

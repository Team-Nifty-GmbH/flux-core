<?php

use FluxErp\Models\Product;
use FluxErp\Settings\ProductSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {});

/**
 * Builds a variant whose inheritable fields default to the parent's current values
 * (simulating a materialized variant that starts as a copy of its parent), then applies
 * $attributes on top. Without this, Product::factory() randomizes every inheritable field
 * independently for parent and variant, so the new value-diff override hook would mark
 * unrelated fields as overridden on creation and shadow the assertion under test.
 */
function variantOf(Product $parent, array $attributes = []): Product
{
    // Skip fields the factory leaves unset (null in-memory, DB-default at insert) — passing
    // an explicit null would override that column default and can violate NOT NULL constraints.
    $matchingParent = collect($parent->getInheritableFields())
        ->mapWithKeys(fn (string $field): array => [$field => $parent->{$field}])
        ->reject(fn (mixed $value): bool => is_null($value))
        ->all();

    return Product::factory()->create(array_merge(
        $matchingParent,
        ['parent_id' => $parent->getKey()],
        $attributes
    ));
}

test('isVariant returns true when parent_id is set', function (): void {
    $parent = Product::factory()->create();
    $variant = Product::factory()->create(['parent_id' => $parent->getKey()]);

    expect($variant->isVariant())->toBeTrue();
    expect($parent->isVariant())->toBeFalse();
});

test('inheritanceEnabled returns false when settings flag is false', function (): void {
    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => false])->save();

    $product = Product::factory()->create();

    expect($product->inheritanceEnabled())->toBeFalse();
});

test('inheritanceEnabled returns true when settings flag is true', function (): void {
    $product = Product::factory()->create();

    expect($product->inheritanceEnabled())->toBeTrue();
});

test('isInheritableField returns true for fields in inheritableFields whitelist', function (): void {
    $product = Product::factory()->create();

    expect($product->isInheritableField('name'))->toBeTrue();
    expect($product->isInheritableField('parent_id'))->toBeFalse();
    expect($product->isInheritableField('product_number'))->toBeFalse();
});

test('isInheritableRelation returns true for relations in inheritableRelations whitelist', function (): void {
    $product = Product::factory()->create();

    expect($product->isInheritableRelation('prices'))->toBeTrue();
    expect($product->isInheritableRelation('orderPositions'))->toBeFalse();
});

test('overrides returns true when field is in overridden_fields', function (): void {
    $parent = Product::factory()->create();
    $variant = variantOf($parent, [
        'name' => $parent->name . ' variant',
        'overridden_fields' => ['name'],
    ]);

    expect($variant->overrides('name'))->toBeTrue();
    expect($variant->overrides('description'))->toBeFalse();
});

test('getInheritableFields returns the configured whitelist', function (): void {
    $product = Product::factory()->create();

    expect($product->getInheritableFields())
        ->toBeArray()
        ->toContain('name', 'unit_id', 'vat_rate_id')
        ->not->toContain('parent_id', 'product_number', 'ean');
});

test('getInheritableRelations returns the configured whitelist', function (): void {
    $product = Product::factory()->create();

    expect($product->getInheritableRelations())
        ->toBeArray()
        ->toContain('prices', 'categories', 'productProperties', 'suppliers')
        ->not->toContain('orderPositions', 'crossSellings', 'tags', 'media');
});

test('returns the variant own column value even when not overridden (materialized, no read-time redirect)', function (): void {
    $parent = Product::factory()->create(['name' => 'Parent Name']);
    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'name' => 'stale variant value',
        'overridden_fields' => null,
    ]);

    expect($variant->name)->toBe('stale variant value');
});

test('returns own value for inheritable field when overridden', function (): void {
    $parent = Product::factory()->create(['name' => 'Parent Name']);
    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'name' => 'own variant value',
        'overridden_fields' => ['name'],
    ]);

    expect($variant->name)->toBe('own variant value');
});

test('returns own value for non-inheritable field even on a variant', function (): void {
    $parent = Product::factory()->create(['product_number' => 'PARENT-001']);
    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'product_number' => 'VARIANT-001',
    ]);

    expect($variant->product_number)->toBe('VARIANT-001');
});

test('non-variant ignores overridden_fields entirely (defensive)', function (): void {
    $product = Product::factory()->create([
        'name' => 'Standalone',
        'overridden_fields' => ['name'],
        'parent_id' => null,
    ]);

    expect($product->name)->toBe('Standalone');
});

test('falls back to own column when feature toggle is off', function (): void {
    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => false])->save();

    $parent = Product::factory()->create(['name' => 'Parent Name']);
    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'name' => 'raw variant value',
        'overridden_fields' => null,
    ]);

    expect($variant->name)->toBe('raw variant value');
});

test('saving a differing inheritable field adds it to overridden_fields', function (): void {
    $parent = Product::factory()->create(['name' => 'Parent Name']);
    $variant = variantOf($parent);

    $variant->name = 'New Name';
    $variant->save();

    expect($variant->fresh()->overridden_fields)->toBe(['name']);
    expect($variant->fresh()->name)->toBe('New Name');
});

test('saving a differing field twice is idempotent — does not duplicate field in overridden_fields', function (): void {
    $parent = Product::factory()->create();
    $variant = variantOf($parent, [
        'name' => 'Override Name',
        'overridden_fields' => ['name'],
    ]);

    $variant->name = 'Another Name';
    $variant->save();

    expect($variant->fresh()->overridden_fields)->toBe(['name']);
});

test('saving a non-inheritable field does not touch overridden_fields', function (): void {
    $parent = Product::factory()->create();
    $variant = variantOf($parent);

    $variant->product_number = 'NEW-NUM';
    $variant->save();

    expect($variant->fresh()->overridden_fields)->toBeNull();
});

test('saving on a non-variant does not touch overridden_fields', function (): void {
    $product = Product::factory()->create(['parent_id' => null]);

    $product->name = 'Updated';
    $product->save();

    expect($product->fresh()->overridden_fields)->toBeNull();
});

test('saving a variant field equal to the parent does not mark it overridden', function (): void {
    $parent = Product::factory()->create(['weight_gram' => 100]);
    $variant = variantOf($parent, ['weight_gram' => 100]);

    $variant->update(['weight_gram' => 100]);

    expect($variant->fresh()->overridden_fields)->toBeNull();
});

test('saving a differing value marks the field overridden', function (): void {
    $parent = Product::factory()->create(['weight_gram' => 100]);
    $variant = variantOf($parent, ['weight_gram' => 100]);

    $variant->update(['weight_gram' => 250]);

    expect($variant->fresh()->overridden_fields)->toBe(['weight_gram']);
});

test('clearing a field to empty string is marked overridden even when parent value is null', function (): void {
    $parent = Product::factory()->create(['description' => null]);
    $variant = variantOf($parent, ['description' => 'own description']);

    $variant->update(['description' => '']);

    expect($variant->fresh()->overridden_fields)->toBe(['description']);
});

test('setting same value as parent auto-clears an existing override (value-diff, not write-time)', function (): void {
    $parent = Product::factory()->create(['name' => 'Same']);
    $variant = variantOf($parent, [
        'name' => 'Different',
        'overridden_fields' => ['name'],
    ]);

    $variant->name = 'Same';
    $variant->save();

    expect($variant->fresh()->overridden_fields)->toBeNull();
});

test('media is intentionally NOT inherited (Spatie escape hatch)', function (): void {
    $parent = Product::factory()->create();
    $parent->addMedia(UploadedFile::fake()->image('p.jpg'))
        ->toMediaCollection('images');

    $variant = Product::factory()->create(['parent_id' => $parent->getKey()]);

    expect($variant->media)->toHaveCount(0);
    expect($variant->getInheritableRelations())->not->toContain('media');
});

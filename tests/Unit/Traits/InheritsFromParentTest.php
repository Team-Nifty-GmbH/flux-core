<?php

use FluxErp\Models\Product;
use FluxErp\Models\Tenant;
use Illuminate\Support\Facades\Cache;

beforeEach(function (): void {
    $this->tenant = Tenant::default();
    $this->tenant->update(['product_variant_inheritance_enabled' => true]);
    Cache::memo()->forget('default_' . morph_alias(Tenant::class));
});

it('isVariant returns true when parent_id is set', function (): void {
    $parent = Product::factory()->create();
    $variant = Product::factory()->create(['parent_id' => $parent->getKey()]);

    expect($variant->isVariant())->toBeTrue();
    expect($parent->isVariant())->toBeFalse();
});

it('inheritanceEnabled returns false when tenant flag is false', function (): void {
    $this->tenant->update(['product_variant_inheritance_enabled' => false]);
    Cache::memo()->forget('default_' . morph_alias(Tenant::class));

    $product = Product::factory()->create();

    expect($product->inheritanceEnabled())->toBeFalse();
});

it('inheritanceEnabled returns true when tenant flag is true', function (): void {
    $product = Product::factory()->create();

    expect($product->inheritanceEnabled())->toBeTrue();
});

it('isInheritableField returns true for fields in inheritableFields whitelist', function (): void {
    $product = Product::factory()->create();

    expect($product->isInheritableField('name'))->toBeTrue();
    expect($product->isInheritableField('parent_id'))->toBeFalse();
    expect($product->isInheritableField('product_number'))->toBeFalse();
});

it('isInheritableRelation returns true for relations in inheritableRelations whitelist', function (): void {
    $product = Product::factory()->create();

    expect($product->isInheritableRelation('prices'))->toBeTrue();
    expect($product->isInheritableRelation('orderPositions'))->toBeFalse();
});

it('overrides returns true when field is in overridden_fields', function (): void {
    $parent = Product::factory()->create();
    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['name'],
    ]);

    expect($variant->overrides('name'))->toBeTrue();
    expect($variant->overrides('description'))->toBeFalse();
});

it('getInheritableFields returns the configured whitelist', function (): void {
    $product = Product::factory()->create();

    expect($product->getInheritableFields())
        ->toBeArray()
        ->toContain('name', 'unit_id', 'vat_rate_id')
        ->not->toContain('parent_id', 'product_number', 'ean');
});

it('getInheritableRelations returns the configured whitelist', function (): void {
    $product = Product::factory()->create();

    expect($product->getInheritableRelations())
        ->toBeArray()
        ->toContain('prices', 'categories', 'productProperties')
        ->not->toContain('orderPositions');
});

it('returns parent value for inheritable field when not overridden', function (): void {
    $parent = Product::factory()->create(['name' => 'Parent Name']);
    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'name' => 'stale variant value',
        'overridden_fields' => null,
    ]);

    expect($variant->name)->toBe('Parent Name');
});

it('returns own value for inheritable field when overridden', function (): void {
    $parent = Product::factory()->create(['name' => 'Parent Name']);
    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'name' => 'own variant value',
        'overridden_fields' => ['name'],
    ]);

    expect($variant->name)->toBe('own variant value');
});

it('returns own value for non-inheritable field even on a variant', function (): void {
    $parent = Product::factory()->create(['product_number' => 'PARENT-001']);
    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'product_number' => 'VARIANT-001',
    ]);

    expect($variant->product_number)->toBe('VARIANT-001');
});

it('non-variant ignores overridden_fields entirely (defensive)', function (): void {
    $product = Product::factory()->create([
        'name' => 'Standalone',
        'overridden_fields' => ['name'],
        'parent_id' => null,
    ]);

    expect($product->name)->toBe('Standalone');
});

it('falls back to own column when feature toggle is off', function (): void {
    $this->tenant->update(['product_variant_inheritance_enabled' => false]);
    Cache::memo()->forget('default_' . morph_alias(Tenant::class));

    $parent = Product::factory()->create(['name' => 'Parent Name']);
    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'name' => 'raw variant value',
        'overridden_fields' => null,
    ]);

    expect($variant->name)->toBe('raw variant value');
});

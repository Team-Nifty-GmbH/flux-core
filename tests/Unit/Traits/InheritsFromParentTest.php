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

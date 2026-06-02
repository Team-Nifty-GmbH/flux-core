<?php

use FluxErp\Models\Product;

beforeEach(function (): void {
    app(FluxErp\Settings\ProductSettings::class)->fill(['variant_inheritance_enabled' => true])->save();
});

test('runs the migration for the default tenant when feature toggle is on', function (): void {
    $parent = Product::factory()->create(['name' => 'Same']);
    Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'name' => 'Same',
    ]);

    $this->artisan('flux:product-variants:migrate-inheritance')
        ->expectsOutputToContain('processed')
        ->assertSuccessful();
});

test('refuses to run when feature toggle is off', function (): void {
    app(FluxErp\Settings\ProductSettings::class)->fill(['variant_inheritance_enabled' => false])->save();

    $this->artisan('flux:product-variants:migrate-inheritance')
        ->expectsOutputToContain('disabled')
        ->assertFailed();
});

test('scopes processing to a single parent when --parent-id is given', function (): void {
    $parentA = Product::factory()->create(['name' => 'A']);
    $inheritedAttributes = array_filter(
        $parentA->only($parentA->getInheritableFields()),
        fn ($value) => $value !== null
    );
    $variantA = Product::factory()->create(array_merge(
        $inheritedAttributes,
        ['parent_id' => $parentA->getKey()]
    ));

    $parentB = Product::factory()->create(['name' => 'B']);
    $variantB = Product::factory()->create([
        'parent_id' => $parentB->getKey(),
        'name' => 'B-other',
    ]);

    $this->artisan('flux:product-variants:migrate-inheritance', ['--parent-id' => $parentA->getKey()])
        ->assertSuccessful();

    expect($variantA->fresh()->overridden_fields)->toBeNull();
    expect($variantB->fresh()->overridden_fields)->toBeNull();
});

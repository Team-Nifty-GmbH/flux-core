<?php

use FluxErp\Models\Product;
use FluxErp\Settings\ProductSettings;
use Illuminate\Support\Facades\Blade;

beforeEach(function (): void {
    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => true])->save();
});

test('renders only slot for non-variant products', function (): void {
    $product = Product::factory()->create(['parent_id' => null]);

    $html = Blade::render(
        '<x-flux::product.inheritance-indicator :product="$product" field="name"><span data-testid="slot">slot</span></x-flux::product.inheritance-indicator>',
        ['product' => $product]
    );

    expect($html)->toContain('data-testid="slot"');
    expect($html)->not->toContain(__('Inherited'));
    expect($html)->not->toContain(__('Overridden'));
});

test('renders inherited badge when variant has not overridden the field', function (): void {
    $parent = Product::factory()->create();
    // The value has to match the parent: the override bookkeeping on save derives
    // overridden_fields from the difference, it does not take the column as given.
    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'name' => $parent->name,
    ]);

    $html = Blade::render(
        '<x-flux::product.inheritance-indicator :product="$variant" field="name"><span data-testid="slot">slot</span></x-flux::product.inheritance-indicator>',
        ['variant' => $variant]
    );

    expect($html)->toContain('data-testid="slot"');
    expect($html)->toContain(__('Inherited'));
    expect($html)->not->toContain(__('Overridden'));
});

test('renders overridden badge and reset button when variant overrides the field', function (): void {
    $parent = Product::factory()->create();
    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'name' => 'override',
    ]);

    $html = Blade::render(
        '<x-flux::product.inheritance-indicator :product="$variant" field="name"><span data-testid="slot">slot</span></x-flux::product.inheritance-indicator>',
        ['variant' => $variant]
    );

    expect($html)->toContain('data-testid="slot"');
    expect($html)->toContain(__('Overridden'));
    expect($html)->toContain('resetFields');
    expect($html)->toContain("'name'");
});

test('renders only slot when inheritance is disabled', function (): void {
    app(ProductSettings::class)->fill(['variant_inheritance_enabled' => false])->save();

    $parent = Product::factory()->create();
    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'name' => 'override',
    ]);

    $html = Blade::render(
        '<x-flux::product.inheritance-indicator :product="$variant" field="name"><span data-testid="slot">slot</span></x-flux::product.inheritance-indicator>',
        ['variant' => $variant]
    );

    expect($html)->toContain('data-testid="slot"');
    expect($html)->not->toContain(__('Inherited'));
    expect($html)->not->toContain(__('Overridden'));
});

test('an explicit overridden flag wins over the products own state', function (): void {
    $parent = Product::factory()->create();
    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => null,
    ]);

    $html = Blade::render(
        '<x-flux::product.inheritance-indicator :product="$variant" field="name" :overridden="true"><span>slot</span></x-flux::product.inheritance-indicator>',
        ['variant' => $variant]
    );

    expect($html)->toContain(__('Overridden'))
        ->and($html)->toContain("resetFields('name')");
});

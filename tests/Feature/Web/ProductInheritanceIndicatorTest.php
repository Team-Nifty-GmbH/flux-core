<?php

use FluxErp\Models\Product;
use FluxErp\Models\Tenant;
use Illuminate\Support\Facades\Blade;

beforeEach(function (): void {
    Tenant::default()->update(['product_variant_inheritance_enabled' => true]);
    Tenant::clearDefaultCache();
});

test('renders only slot for non-variant products', function (): void {
    $product = Product::factory()->create(['parent_id' => null]);

    $html = Blade::render(
        '<x-flux::product.inheritance-indicator :product="$product" field="name"><span data-testid="slot">slot</span></x-flux::product.inheritance-indicator>',
        ['product' => $product]
    );

    expect($html)->toContain('data-testid="slot"');
    expect($html)->not->toContain('Vererbt');
    expect($html)->not->toContain('Überschrieben');
});

test('renders inherited badge when variant has not overridden the field', function (): void {
    $parent = Product::factory()->create();
    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => null,
    ]);

    $html = Blade::render(
        '<x-flux::product.inheritance-indicator :product="$variant" field="name"><span data-testid="slot">slot</span></x-flux::product.inheritance-indicator>',
        ['variant' => $variant]
    );

    expect($html)->toContain('data-testid="slot"');
    expect($html)->toContain('Vererbt');
    expect($html)->not->toContain('Überschrieben');
});

test('renders overridden badge and reset button when variant overrides the field', function (): void {
    $parent = Product::factory()->create();
    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['name'],
    ]);

    $html = Blade::render(
        '<x-flux::product.inheritance-indicator :product="$variant" field="name"><span data-testid="slot">slot</span></x-flux::product.inheritance-indicator>',
        ['variant' => $variant]
    );

    expect($html)->toContain('data-testid="slot"');
    expect($html)->toContain('Überschrieben');
    expect($html)->toContain('resetField');
    expect($html)->toContain("'name'");
});

test('renders only slot when inheritance is disabled tenant-wide', function (): void {
    Tenant::default()->update(['product_variant_inheritance_enabled' => false]);
    Tenant::clearDefaultCache();

    $parent = Product::factory()->create();
    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => ['name'],
    ]);

    $html = Blade::render(
        '<x-flux::product.inheritance-indicator :product="$variant" field="name"><span data-testid="slot">slot</span></x-flux::product.inheritance-indicator>',
        ['variant' => $variant]
    );

    expect($html)->toContain('data-testid="slot"');
    expect($html)->not->toContain('Vererbt');
    expect($html)->not->toContain('Überschrieben');
});

test('uses custom reset method when reset-method prop is set', function (): void {
    $parent = Product::factory()->create();
    $variant = Product::factory()->create([
        'parent_id' => $parent->getKey(),
        'overridden_fields' => null,
    ]);

    $template = '<x-flux::product.inheritance-indicator'
        . ' :product="$variant"'
        . ' field="price:42"'
        . ' reset-method="resetPriceListEntry"'
        . ' :overridden="true"'
        . '><span>slot</span></x-flux::product.inheritance-indicator>';

    $html = Blade::render($template, ['variant' => $variant]);

    expect($html)->toContain('resetPriceListEntry');
    expect($html)->toContain("'price:42'");
});

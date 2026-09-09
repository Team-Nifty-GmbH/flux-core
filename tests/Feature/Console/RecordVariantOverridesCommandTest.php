<?php

use FluxErp\Models\AttributeTranslation;
use FluxErp\Models\Language;
use FluxErp\Models\Product;
use FluxErp\Models\VatRate;

beforeEach(function (): void {
    $this->vatRate = VatRate::default() ?? VatRate::factory()->create(['is_default' => true]);

    $this->parent = Product::factory()->create([
        'vat_rate_id' => $this->vatRate->getKey(),
        'name' => 'Parent name',
        'is_shipping_free' => false,
    ]);
});

function legacyVariant(array $attributes = []): Product
{
    $variant = Product::factory()->create(array_merge([
        'parent_id' => test()->parent->getKey(),
        'vat_rate_id' => test()->vatRate->getKey(),
    ], $attributes));

    // a variant from before the upgrade knows nothing about its own values
    Product::query()->whereKey($variant->getKey())->update(['overridden_fields' => null]);

    return $variant->refresh();
}

test('records the fields a variant already carries itself', function (): void {
    $variant = legacyVariant(['name' => 'Variant own name', 'is_shipping_free' => true]);

    $this->artisan('flux:product-variants:record-overrides')->assertSuccessful();

    expect($variant->refresh()->overridden_fields)
        ->toContain('name')
        ->toContain('is_shipping_free');
});

test('leaves a field alone that matches the parent', function (): void {
    $variant = legacyVariant(['name' => 'Parent name', 'is_shipping_free' => true]);

    $this->artisan('flux:product-variants:record-overrides')->assertSuccessful();

    expect($variant->refresh()->overridden_fields)
        ->not->toContain('name')
        ->toContain('is_shipping_free');
});

test('the recorded fields survive the next save of the parent', function (): void {
    $variant = legacyVariant(['name' => 'Variant own name']);

    $this->artisan('flux:product-variants:record-overrides')->assertSuccessful();

    $this->parent->name = 'Parent renamed';
    $this->parent->save();

    expect($variant->refresh()->name)->toBe('Variant own name');
});

test('dry run reports without writing', function (): void {
    $variant = legacyVariant(['name' => 'Variant own name']);

    $this->artisan('flux:product-variants:record-overrides', ['--dry-run' => true])
        ->assertSuccessful();

    expect($variant->refresh()->overridden_fields)->toBeNull();
});

test('keeps overrides that were already recorded', function (): void {
    $variant = legacyVariant(['name' => 'Variant own name']);
    Product::query()->whereKey($variant->getKey())->update(['overridden_fields' => ['description']]);

    $this->artisan('flux:product-variants:record-overrides')->assertSuccessful();

    expect($variant->refresh()->overridden_fields)
        ->toContain('description')
        ->toContain('name');
});

test('records a field whose translation already reads as the parent value', function (): void {
    if (! Language::default()) {
        Language::factory()->create(['language_code' => 'en', 'is_default' => true]);
    }

    $language = Language::query()->firstWhere('language_code', 'de')
        ?? Language::factory()->create(['language_code' => 'de']);

    app()->setLocale('de');

    $variant = legacyVariant(['name' => 'Variant own name']);

    // inheritance copies the parent translation onto a variant that carries no override,
    // so reading the attribute hands back the parent name while the column keeps its own
    AttributeTranslation::query()->create([
        'model_type' => $variant->getMorphClass(),
        'model_id' => $variant->getKey(),
        'language_id' => $language->getKey(),
        'attribute' => 'name',
        'value' => $this->parent->name,
    ]);

    expect($variant->fresh()->getAttribute('name'))->toBe($this->parent->name)
        ->and($variant->fresh()->getRawOriginal('name'))->toBe('Variant own name');

    $this->artisan('flux:product-variants:record-overrides')->assertSuccessful();

    expect($variant->refresh()->overridden_fields)->toContain('name');
});

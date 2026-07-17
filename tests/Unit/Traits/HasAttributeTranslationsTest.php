<?php

use FluxErp\Models\Language;
use FluxErp\Models\Product;

test('getTranslatableAttributes returns array', function (): void {
    $product = Product::factory()->create();

    $translatable = $product->getTranslatableAttributes();

    expect($translatable)->toBeArray()->toContain('name', 'description');
});

test('model can be localized', function (): void {
    $product = Product::factory()->create(['name' => 'English Name']);

    // Store a translation
    $product->attributeTranslations()->create([
        'language_id' => $this->defaultLanguage->getKey(),
        'attribute' => 'name',
        'value' => 'Translated Name',
    ]);

    $product->localize($this->defaultLanguage->getKey());

    expect($product->name)->toBe('Translated Name');
});

test('retrieved hook localizes model based on app locale when session is empty', function (): void {
    $language = Language::factory()->create(['language_code' => 'fr']);

    $product = Product::factory()->create(['name' => 'English Name']);

    $product->attributeTranslations()->create([
        'language_id' => $language->getKey(),
        'attribute' => 'name',
        'value' => 'Nom Français',
    ]);

    // No session set, but app locale is set to the language code
    app()->setLocale('fr');

    $freshProduct = Product::query()->find($product->getKey());

    expect($freshProduct->name)->toBe('Nom Français');
});

test('localize falls back to app locale when no language id given and session is empty', function (): void {
    $language = Language::factory()->create(['language_code' => 'de']);

    $product = Product::factory()->create(['name' => 'English Name']);

    $product->attributeTranslations()->create([
        'language_id' => $language->getKey(),
        'attribute' => 'name',
        'value' => 'Deutscher Name',
    ]);

    app()->setLocale('de');

    $product->localize();

    expect($product->name)->toBe('Deutscher Name');
});

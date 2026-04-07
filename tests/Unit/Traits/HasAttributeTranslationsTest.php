<?php

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

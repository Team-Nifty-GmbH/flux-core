<?php

use FluxErp\Actions\AttributeTranslation\DeleteAttributeTranslation;
use FluxErp\Actions\AttributeTranslation\UpsertAttributeTranslation;
use FluxErp\Models\Product;

beforeEach(function (): void {
    $this->product = Product::factory()->create();
});

test('upsert creates new attribute translation', function (): void {
    $translation = UpsertAttributeTranslation::make([
        'language_id' => $this->defaultLanguage->getKey(),
        'model_type' => morph_alias(Product::class),
        'model_id' => $this->product->getKey(),
        'attribute' => 'name',
        'value' => 'Translated Name',
    ])->validate()->execute();

    expect($translation->value)->toBe('Translated Name');
});

test('upsert updates existing attribute translation', function (): void {
    $first = UpsertAttributeTranslation::make([
        'language_id' => $this->defaultLanguage->getKey(),
        'model_type' => morph_alias(Product::class),
        'model_id' => $this->product->getKey(),
        'attribute' => 'name',
        'value' => 'First',
    ])->validate()->execute();

    $updated = UpsertAttributeTranslation::make([
        'id' => $first->getKey(),
        'value' => 'Updated',
    ])->validate()->execute();

    expect($updated->value)->toBe('Updated');
});

test('delete attribute translation', function (): void {
    $translation = UpsertAttributeTranslation::make([
        'language_id' => $this->defaultLanguage->getKey(),
        'model_type' => morph_alias(Product::class),
        'model_id' => $this->product->getKey(),
        'attribute' => 'description',
        'value' => 'To delete',
    ])->validate()->execute();

    expect(DeleteAttributeTranslation::make(['id' => $translation->getKey()])
        ->validate()->execute())->toBeTrue();
});

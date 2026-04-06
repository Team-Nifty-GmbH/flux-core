<?php

use FluxErp\Models\Product;
use FluxErp\Rules\Translatable;
use Illuminate\Support\Facades\Validator;

test('translatable attribute passes', function (): void {
    $passes = Validator::make(
        [
            'model_type' => morph_alias(Product::class),
            'attribute' => 'name',
        ],
        ['attribute' => app(Translatable::class)]
    )->passes();

    expect($passes)->toBeTrue();
});

test('non-translatable attribute fails', function (): void {
    $passes = Validator::make(
        [
            'model_type' => morph_alias(Product::class),
            'attribute' => 'id',
        ],
        ['attribute' => app(Translatable::class)]
    )->passes();

    expect($passes)->toBeFalse();
});

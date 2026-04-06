<?php

use FluxErp\Models\Currency;
use FluxErp\Rules\Sole;
use Illuminate\Support\Facades\Validator;

test('sole passes when value is unique', function (): void {
    $currency = Currency::factory()->create(['iso' => 'UNIQUE_TEST']);

    $passes = Validator::make(
        ['iso' => 'UNIQUE_TEST'],
        ['iso' => app(Sole::class, ['model' => Currency::class])]
    )->passes();

    expect($passes)->toBeTrue();
});

test('sole fails when multiple records match', function (): void {
    Currency::factory()->count(2)->create(['name' => 'Duplicate Name']);

    $passes = Validator::make(
        ['name' => 'Duplicate Name'],
        ['name' => app(Sole::class, ['model' => Currency::class])]
    )->passes();

    expect($passes)->toBeFalse();
});

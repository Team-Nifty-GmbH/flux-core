<?php

use FluxErp\Models\Currency;
use FluxErp\Rules\ModelDoesntExist;
use FluxErp\Rules\ModelExists;
use Illuminate\Support\Facades\Validator;

test('ModelExists passes for existing model', function (): void {
    $currency = Currency::factory()->create();

    $passes = Validator::make(
        ['id' => $currency->getKey()],
        ['id' => app(ModelExists::class, ['model' => Currency::class])]
    )->passes();

    expect($passes)->toBeTrue();
});

test('ModelExists fails for non-existing model', function (): void {
    $nonExistingId = (Currency::query()->max('id') ?? 0) + 9999;

    $passes = Validator::make(
        ['id' => $nonExistingId],
        ['id' => app(ModelExists::class, ['model' => Currency::class])]
    )->passes();

    expect($passes)->toBeFalse();
});

test('ModelDoesntExist passes when model does not exist', function (): void {
    $nonExistingId = (Currency::query()->max('id') ?? 0) + 9999;

    $passes = Validator::make(
        ['id' => $nonExistingId],
        ['id' => app(ModelDoesntExist::class, ['model' => Currency::class])]
    )->passes();

    expect($passes)->toBeTrue();
});

test('ModelDoesntExist fails when model exists', function (): void {
    $currency = Currency::factory()->create();

    $passes = Validator::make(
        ['id' => $currency->getKey()],
        ['id' => app(ModelDoesntExist::class, ['model' => Currency::class])]
    )->passes();

    expect($passes)->toBeFalse();
});

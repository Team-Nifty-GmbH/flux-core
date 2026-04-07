<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Enums\SalutationEnum;
use FluxErp\Rules\EnumRule;
use Illuminate\Support\Facades\Validator;

test('backed enum value passes', function (): void {
    $passes = Validator::make(
        ['type' => 'order'],
        ['type' => app(EnumRule::class, ['type' => OrderTypeEnum::class])]
    )->passes();

    expect($passes)->toBeTrue();
});

test('invalid backed enum value fails', function (): void {
    $passes = Validator::make(
        ['type' => 'nonexistent'],
        ['type' => app(EnumRule::class, ['type' => OrderTypeEnum::class])]
    )->passes();

    expect($passes)->toBeFalse();
});

test('flux enum const value passes', function (): void {
    $passes = Validator::make(
        ['sal' => 'mr'],
        ['sal' => app(EnumRule::class, ['type' => SalutationEnum::class])]
    )->passes();

    expect($passes)->toBeTrue();
});

test('invalid flux enum const value fails', function (): void {
    $passes = Validator::make(
        ['sal' => 'nonexistent'],
        ['sal' => app(EnumRule::class, ['type' => SalutationEnum::class])]
    )->passes();

    expect($passes)->toBeFalse();
});

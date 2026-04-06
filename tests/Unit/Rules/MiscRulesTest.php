<?php

use FluxErp\Rules\ClassExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\StringOrInteger;
use FluxErp\Rules\ViewExists;
use Illuminate\Support\Facades\Validator;

test('ClassExists passes for existing class', function (): void {
    $passes = Validator::make(
        ['class' => FluxErp\Models\Order::class],
        ['class' => app(ClassExists::class)]
    )->passes();

    expect($passes)->toBeTrue();
});

test('ClassExists fails for non-existing class', function (): void {
    $passes = Validator::make(
        ['class' => 'NonExistent\\Class\\Name'],
        ['class' => app(ClassExists::class)]
    )->passes();

    expect($passes)->toBeFalse();
});

test('MorphClassExists passes for valid morph alias', function (): void {
    $passes = Validator::make(
        ['type' => morph_alias(FluxErp\Models\Order::class)],
        ['type' => app(MorphClassExists::class)]
    )->passes();

    expect($passes)->toBeTrue();
});

test('MorphClassExists fails for invalid morph alias', function (): void {
    $passes = Validator::make(
        ['type' => 'nonexistent-morph'],
        ['type' => app(MorphClassExists::class)]
    )->passes();

    expect($passes)->toBeFalse();
});

test('StringOrInteger passes for string', function (): void {
    $passes = Validator::make(
        ['val' => 'hello'],
        ['val' => app(StringOrInteger::class)]
    )->passes();

    expect($passes)->toBeTrue();
});

test('StringOrInteger passes for positive integer', function (): void {
    $passes = Validator::make(
        ['val' => 42],
        ['val' => app(StringOrInteger::class)]
    )->passes();

    expect($passes)->toBeTrue();
});

test('StringOrInteger unsigned rejects negative', function (): void {
    $passes = Validator::make(
        ['val' => -1],
        ['val' => app(StringOrInteger::class, ['unsigned' => true])]
    )->passes();

    expect($passes)->toBeFalse();
});

test('StringOrInteger non-unsigned allows negative', function (): void {
    $passes = Validator::make(
        ['val' => -1],
        ['val' => app(StringOrInteger::class, ['unsigned' => false])]
    )->passes();

    expect($passes)->toBeTrue();
});

test('ViewExists passes for existing view', function (): void {
    $passes = Validator::make(
        ['view' => 'flux::livewire.navigation'],
        ['view' => app(ViewExists::class)]
    )->passes();

    expect($passes)->toBeTrue();
});

test('ViewExists fails for non-existing view', function (): void {
    $passes = Validator::make(
        ['view' => 'nonexistent.view.name'],
        ['view' => app(ViewExists::class)]
    )->passes();

    expect($passes)->toBeFalse();
});

<?php

use FluxErp\Rules\ArrayIsKeyValuePair;
use FluxErp\Rules\ArrayIsList;
use Illuminate\Support\Facades\Validator;

test('ArrayIsKeyValuePair passes for string key-value pairs', function (): void {
    $passes = Validator::make(
        ['data' => ['key1' => 'value1', 'key2' => 'value2']],
        ['data' => app(ArrayIsKeyValuePair::class)]
    )->passes();

    expect($passes)->toBeTrue();
});

test('ArrayIsKeyValuePair fails for non-array', function (): void {
    $passes = Validator::make(
        ['data' => 'not-array'],
        ['data' => app(ArrayIsKeyValuePair::class)]
    )->passes();

    expect($passes)->toBeFalse();
});

test('ArrayIsKeyValuePair fails for empty values', function (): void {
    $passes = Validator::make(
        ['data' => ['key' => '']],
        ['data' => app(ArrayIsKeyValuePair::class)]
    )->passes();

    expect($passes)->toBeFalse();
});

test('ArrayIsList passes for string list', function (): void {
    $passes = Validator::make(
        ['data' => ['a', 'b', 'c']],
        ['data' => app(ArrayIsList::class)]
    )->passes();

    expect($passes)->toBeTrue();
});

test('ArrayIsList passes for integer list', function (): void {
    $passes = Validator::make(
        ['data' => [1, 2, 3]],
        ['data' => app(ArrayIsList::class)]
    )->passes();

    expect($passes)->toBeTrue();
});

test('ArrayIsList fails for associative array', function (): void {
    $passes = Validator::make(
        ['data' => ['key' => 'value']],
        ['data' => app(ArrayIsList::class)]
    )->passes();

    expect($passes)->toBeFalse();
});

test('ArrayIsList fails for non-array', function (): void {
    $passes = Validator::make(
        ['data' => 'string'],
        ['data' => app(ArrayIsList::class)]
    )->passes();

    expect($passes)->toBeFalse();
});

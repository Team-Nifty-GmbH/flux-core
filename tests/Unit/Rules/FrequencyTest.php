<?php

use FluxErp\Rules\Frequency;
use Illuminate\Support\Facades\Validator;

test('time window passes for between and unlessBetween', function (string $method, array $window): void {
    $passes = Validator::make(
        ['method' => $method, 'parameters' => $window],
        ['parameters' => new Frequency('method')]
    )->passes();

    expect($passes)->toBeTrue();
})
    ->with(['between', 'unlessBetween'])
    ->with([
        'same day' => [['05:00', '22:00']],
        'across midnight' => [['22:00', '05:00']],
    ]);

test('time window fails for invalid times', function (string $method, array $window): void {
    $passes = Validator::make(
        ['method' => $method, 'parameters' => $window],
        ['parameters' => new Frequency('method')]
    )->passes();

    expect($passes)->toBeFalse();
})
    ->with(['between', 'unlessBetween'])
    ->with([
        'single entry' => [['05:00']],
        'hour out of range' => [['05:00', '24:00']],
        'not a time' => [['05:00', 'late']],
    ]);

<?php

use FluxErp\Rules\AvailableValidationRule;
use Illuminate\Support\Facades\Validator;

beforeEach(function (): void {
    $this->validate = fn (string $value) => Validator::make(
        ['rule' => $value],
        ['rule' => app(AvailableValidationRule::class)]
    )->passes();
});

test('common rules pass', function (): void {
    expect(($this->validate)('required'))->toBeTrue();
    expect(($this->validate)('string'))->toBeTrue();
    expect(($this->validate)('integer'))->toBeTrue();
    expect(($this->validate)('boolean'))->toBeTrue();
    expect(($this->validate)('email'))->toBeTrue();
    expect(($this->validate)('nullable'))->toBeTrue();
});

test('parameterized rules pass', function (): void {
    expect(($this->validate)('max:255'))->toBeTrue();
    expect(($this->validate)('min:1'))->toBeTrue();
    expect(($this->validate)('after:2026-01-01'))->toBeTrue();
});

test('unknown rule fails', function (): void {
    expect(($this->validate)('nonexistent_rule'))->toBeFalse();
});

test('trailing colon fails', function (): void {
    expect(($this->validate)('required:'))->toBeFalse();
});

test('pipe character fails', function (): void {
    expect(($this->validate)('required|string'))->toBeFalse();
});

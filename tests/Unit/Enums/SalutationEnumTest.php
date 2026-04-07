<?php

use FluxErp\Enums\SalutationEnum;

test('gender returns correct mapping', function (): void {
    expect(SalutationEnum::gender(SalutationEnum::Mr))->toBe('male');
    expect(SalutationEnum::gender(SalutationEnum::Mrs))->toBe('female');
});

test('toArray returns all values', function (): void {
    expect(SalutationEnum::toArray())->toBeArray()->not->toBeEmpty();
});

test('values include mr and mrs', function (): void {
    expect(SalutationEnum::values())->toContain('mr', 'mrs');
});

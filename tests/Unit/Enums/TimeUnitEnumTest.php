<?php

use FluxErp\Enums\TimeUnitEnum;

test('convert hours from minutes', function (): void {
    expect(TimeUnitEnum::Hour->convertFromMinutes(120))->toEqual(2.0);
});

test('convert days from hours', function (): void {
    expect(TimeUnitEnum::Day->convertFromHours(48))->toEqual(2.0);
});

test('convert seconds from milliseconds', function (): void {
    expect(TimeUnitEnum::Second->convertFromMilliseconds(5000))->toEqual(5.0);
});

test('toArray returns all units', function (): void {
    expect(TimeUnitEnum::toArray())->toBeArray()->not->toBeEmpty();
});

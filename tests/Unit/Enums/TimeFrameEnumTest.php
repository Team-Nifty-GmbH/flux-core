<?php

use FluxErp\Enums\TimeFrameEnum;

test('toArray returns all timeframes', function (): void {
    $array = TimeFrameEnum::toArray();

    expect($array)->toBeArray()->not->toBeEmpty();
});

test('values include this month and this year', function (): void {
    $values = TimeFrameEnum::values();

    expect($values)->toContain('This Month', 'This Year');
});

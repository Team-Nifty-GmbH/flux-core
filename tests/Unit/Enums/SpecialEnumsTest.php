<?php

use FluxErp\Enums\ChartColorEnum;
use FluxErp\Enums\ComparisonTypeEnum;
use FluxErp\Enums\EditorColorPaletteEnum;
use FluxErp\Enums\GrowthRateTypeEnum;

test('ChartColorEnum has values', function (): void {
    expect(ChartColorEnum::toArray())->toBeArray()->not->toBeEmpty();
});

test('ComparisonTypeEnum has values', function (): void {
    expect(ComparisonTypeEnum::toArray())->toBeArray()->not->toBeEmpty();
});

test('EditorColorPaletteEnum getColorFamilies returns array', function (): void {
    $families = EditorColorPaletteEnum::getColorFamilies();

    expect($families)->toBeArray()->not->toBeEmpty();
});

test('GrowthRateTypeEnum getValue calculates growth', function (): void {
    $result = GrowthRateTypeEnum::Percentage->getValue(100, 120);

    expect($result)->toBeNumeric();
});

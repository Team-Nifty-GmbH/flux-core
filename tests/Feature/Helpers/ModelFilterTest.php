<?php

use FluxErp\Helpers\ModelFilter;
use FluxErp\Models\Currency;

beforeEach(function (): void {
    Currency::factory()->count(3)->create();
});

test('filterModel returns results for valid model', function (): void {
    $result = ModelFilter::filterModel(Currency::class);

    expect($result)->toBeArray()
        ->toHaveKey('data');
});

test('filterModel with sort returns ordered results', function (): void {
    $result = ModelFilter::filterModel(
        Currency::class,
        sort: 'name|asc'
    );

    expect($result['data'])->not->toBeEmpty();
});

test('filterModel returns empty for invalid model', function (): void {
    $result = ModelFilter::filterModel('NonExistent\\Model');

    expect($result)->toBeEmpty();
});

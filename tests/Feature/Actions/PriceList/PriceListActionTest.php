<?php

use FluxErp\Actions\PriceList\CreatePriceList;
use FluxErp\Actions\PriceList\DeletePriceList;
use FluxErp\Actions\PriceList\UpdatePriceList;
use FluxErp\Models\PriceList;

test('create price list', function (): void {
    $list = CreatePriceList::make([
        'name' => 'Retail',
        'price_list_code' => 'RET',
        'is_net' => true,
        'rounding_method_enum' => 'none',
    ])->validate()->execute();

    expect($list)->toBeInstanceOf(PriceList::class)
        ->name->toBe('Retail')
        ->price_list_code->toBe('RET');
});

test('create price list requires name code and is_net', function (): void {
    CreatePriceList::assertValidationErrors([], ['name', 'price_list_code', 'is_net']);
});

test('update price list', function (): void {
    $list = CreatePriceList::make([
        'name' => 'Original',
        'price_list_code' => 'ORI',
        'is_net' => true,
        'rounding_method_enum' => 'none',
    ])->validate()->execute();

    $updated = UpdatePriceList::make([
        'id' => $list->getKey(),
        'name' => 'Wholesale',
        'rounding_method_enum' => 'none',
    ])->validate()->execute();

    expect($updated->name)->toBe('Wholesale');
});

test('delete price list', function (): void {
    $list = CreatePriceList::make([
        'name' => 'Temp',
        'price_list_code' => 'TMP',
        'is_net' => true,
        'rounding_method_enum' => 'none',
    ])->validate()->execute();

    expect(DeletePriceList::make(['id' => $list->getKey()])
        ->validate()->execute())->toBeTrue();
});

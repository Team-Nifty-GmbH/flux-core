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

test('update price list without discount keeps the discount of a derived price list', function (): void {
    $parent = PriceList::factory()->create();
    $list = CreatePriceList::make([
        'parent_id' => $parent->getKey(),
        'name' => 'Derived',
        'price_list_code' => 'DER',
        'is_net' => true,
        'rounding_method_enum' => 'none',
        'discount' => ['discount' => 0.1, 'is_percentage' => true],
    ])->validate()->execute();

    UpdatePriceList::make([
        'id' => $list->getKey(),
        'rounding_method_enum' => 'none',
        'name' => 'Renamed',
    ])->validate()->execute();

    expect($list->discount()->first())
        ->not->toBeNull()
        ->discount->toEqual(0.1);
});

test('update price list changes the discount without repeating parent_id', function (): void {
    $parent = PriceList::factory()->create();
    $list = CreatePriceList::make([
        'parent_id' => $parent->getKey(),
        'name' => 'Derived',
        'price_list_code' => 'DER',
        'is_net' => true,
        'rounding_method_enum' => 'none',
        'discount' => ['discount' => 0.1, 'is_percentage' => true],
    ])->validate()->execute();

    UpdatePriceList::make([
        'id' => $list->getKey(),
        'rounding_method_enum' => 'none',
        'discount' => ['discount' => 0.2, 'is_percentage' => true],
    ])->validate()->execute();

    expect($list->discount()->first()->discount)->toEqual(0.2);
});

test('update price list removes the discount when the parent is removed', function (): void {
    $parent = PriceList::factory()->create();
    $list = CreatePriceList::make([
        'parent_id' => $parent->getKey(),
        'name' => 'Derived',
        'price_list_code' => 'DER',
        'is_net' => true,
        'rounding_method_enum' => 'none',
        'discount' => ['discount' => 0.1, 'is_percentage' => true],
    ])->validate()->execute();

    UpdatePriceList::make([
        'id' => $list->getKey(),
        'rounding_method_enum' => 'none',
        'parent_id' => null,
        'discount' => ['discount' => 0.1, 'is_percentage' => true],
    ])->validate()->execute();

    expect($list->discount()->exists())->toBeFalse();
});

test('update price list removes the discount when it is cleared', function (): void {
    $parent = PriceList::factory()->create();
    $list = CreatePriceList::make([
        'parent_id' => $parent->getKey(),
        'name' => 'Derived',
        'price_list_code' => 'DER',
        'is_net' => true,
        'rounding_method_enum' => 'none',
        'discount' => ['discount' => 0.1, 'is_percentage' => true],
    ])->validate()->execute();

    UpdatePriceList::make([
        'id' => $list->getKey(),
        'rounding_method_enum' => 'none',
        'parent_id' => $parent->getKey(),
        'discount' => ['discount' => null, 'is_percentage' => true],
    ])->validate()->execute();

    expect($list->discount()->exists())->toBeFalse();
});

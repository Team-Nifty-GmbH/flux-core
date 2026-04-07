<?php

use FluxErp\Models\Order;
use FluxErp\Models\Product;

test('getColumns returns non-empty collection', function (): void {
    $columns = Product::getColumns();

    expect($columns)->toBeInstanceOf(Illuminate\Support\Collection::class)
        ->not->toBeEmpty();
});

test('getColumns with showHidden returns more columns', function (): void {
    $normal = Product::getColumns()->count();
    $withHidden = Product::getColumns(showHidden: true)->count();

    expect($withHidden)->toBeGreaterThanOrEqual($normal);
});

test('relationships returns array of relation names', function (): void {
    $relations = Order::relationships();

    expect($relations)->toBeArray()->not->toBeEmpty();
});

<?php

use FluxErp\Livewire\DataTables\ProductList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ProductList::class)
        ->assertOk();
});

test('product type is shown as translated label', function (): void {
    app()->setLocale('de');

    $component = new ProductList();
    $method = new ReflectionMethod($component, 'augmentItemArray');

    $itemArray = ['product_type' => 'product'];
    $method->invokeArgs($component, [&$itemArray, new FluxErp\Models\Product()]);

    expect($itemArray['product_type'])->toBe('Produkt');
});

<?php

use FluxErp\Livewire\Forms\ProductForm;
use FluxErp\Livewire\Product\WarehouseList;
use FluxErp\Models\Product;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $product = Product::factory()->create();

    $form = new ProductForm(Livewire::new(WarehouseList::class), 'product');
    $form->fill($product);

    Livewire::test(WarehouseList::class, ['product' => $form])
        ->assertOk();
});

<?php

use FluxErp\Livewire\Forms\ProductForm;
use FluxErp\Livewire\Product\VariantList;
use FluxErp\Models\Product;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $product = Product::factory()->create();
    $form = new ProductForm(Livewire::new(VariantList::class), 'product');
    $form->fill($product);

    Livewire::test(VariantList::class, ['product' => $form])
        ->assertOk();
});

<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Forms\ProductForm;
use FluxErp\Livewire\Product\VariantList;
use FluxErp\Models\Product;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->product = Product::factory()->create();
});

test('renders successfully', function (): void {
    $form = new ProductForm(Livewire::new(VariantList::class), 'product');
    $form->fill($this->product);

    Livewire::test(VariantList::class, ['product' => $form])
        ->assertStatus(200);
});

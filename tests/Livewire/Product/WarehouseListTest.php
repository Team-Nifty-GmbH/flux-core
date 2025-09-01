<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Forms\ProductForm;
use FluxErp\Livewire\Product\WarehouseList;
use FluxErp\Models\Product;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->product = Product::factory()->create();
});

test('renders successfully', function (): void {
    $form = new ProductForm(Livewire::new(WarehouseList::class), 'product');
    $form->fill($this->product);

    Livewire::test(WarehouseList::class, ['product' => $form])
        ->assertStatus(200);
});

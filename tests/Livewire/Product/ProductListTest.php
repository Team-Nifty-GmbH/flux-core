<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Product\ProductList;
use FluxErp\Models\Product;
use Livewire\Livewire;

test('can add products to cart', function (): void {
    $products = Product::factory()->count(3)->create();

    Livewire::test(ProductList::class)
        ->set('selected', [$products->first()->id])
        ->call('addSelectedToCart')
        ->assertDispatched('cart:add', [$products->first()->id])
        ->assertSet('selected', []);
});

test('renders successfully', function (): void {
    Livewire::test(ProductList::class)
        ->assertStatus(200);
});

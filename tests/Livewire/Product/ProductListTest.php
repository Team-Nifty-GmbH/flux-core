<?php

namespace FluxErp\Tests\Livewire\Product;

use FluxErp\Livewire\Product\ProductList;
use FluxErp\Models\Product;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ProductListTest extends TestCase
{
    public function test_can_add_products_to_cart(): void
    {
        $products = Product::factory()->count(3)->create();

        Livewire::test(ProductList::class)
            ->set('selected', [$products->first()->id])
            ->call('addSelectedToCart')
            ->assertDispatched('cart:add', [$products->first()->id])
            ->assertSet('selected', []);
    }

    public function test_renders_successfully(): void
    {
        Livewire::test(ProductList::class)
            ->assertStatus(200);
    }
}

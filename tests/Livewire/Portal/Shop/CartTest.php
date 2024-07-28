<?php

namespace FluxErp\Tests\Livewire\Portal\Shop;

use FluxErp\Livewire\Portal\Shop\Cart;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class CartTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(Cart::class)
            ->assertStatus(200);
    }
}

<?php

namespace Tests\Feature\Livewire\Portal\Shop;

use FluxErp\Livewire\Portal\Shop\Cart;
use Livewire\Livewire;
use Tests\TestCase;

class CartTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(Cart::class)
            ->assertStatus(200);
    }
}

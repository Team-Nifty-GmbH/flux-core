<?php

namespace FluxErp\Tests\Livewire\Portal\Shop;

use FluxErp\Livewire\Portal\Shop\Cart;
use FluxErp\Models\Currency;
use FluxErp\Models\PriceList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class CartTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        PriceList::factory()->create([
            'is_default' => true,
        ]);
        Currency::factory()->create([
            'is_default' => true,
        ]);
    }

    public function test_renders_successfully()
    {
        Livewire::test(Cart::class)
            ->assertStatus(200);
    }
}

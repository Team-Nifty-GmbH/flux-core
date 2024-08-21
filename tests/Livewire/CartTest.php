<?php

namespace FluxErp\Tests\Livewire;

use FluxErp\Livewire\Cart;
use FluxErp\Models\Currency;
use FluxErp\Models\PriceList;
use Livewire\Livewire;

class CartTest extends BaseSetup
{
    protected function setUp(): void
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

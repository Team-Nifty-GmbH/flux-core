<?php

namespace Tests\Feature\Livewire\Portal\Shop;

use FluxErp\Livewire\Portal\Shop\ProductListCard;
use Livewire\Livewire;
use Tests\TestCase;

class ProductListCardTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(ProductListCard::class)
            ->assertStatus(200);
    }
}

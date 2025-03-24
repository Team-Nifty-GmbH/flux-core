<?php

namespace FluxErp\Tests\Livewire\Portal\Shop;

use FluxErp\Livewire\Portal\Shop\ProductListCard;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ProductListCardTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(ProductListCard::class)
            ->assertStatus(200);
    }
}

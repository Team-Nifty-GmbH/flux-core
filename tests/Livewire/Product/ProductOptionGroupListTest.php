<?php

namespace FluxErp\Tests\Livewire\Product;

use FluxErp\Livewire\Product\ProductOptionGroupList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ProductOptionGroupListTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(ProductOptionGroupList::class)
            ->assertStatus(200);
    }
}

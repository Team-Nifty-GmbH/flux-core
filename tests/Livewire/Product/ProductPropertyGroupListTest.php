<?php

namespace FluxErp\Tests\Livewire\DataTablesProduct;

use FluxErp\Livewire\Product\ProductPropertyGroupList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ProductPropertyGroupListTest extends TestCase
{
    protected string $livewireComponent = ProductPropertyGroupList::class;

    public function test_renders_successfully()
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

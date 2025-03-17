<?php

namespace FluxErp\Tests\Livewire\Product;

use FluxErp\Livewire\Product\BundleList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class BundleListTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(BundleList::class)
            ->assertStatus(200);
    }
}

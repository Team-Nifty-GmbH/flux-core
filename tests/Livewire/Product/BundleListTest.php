<?php

namespace Tests\Feature\Livewire\Product;

use FluxErp\Livewire\Product\BundleList;
use Livewire\Livewire;
use Tests\TestCase;

class BundleListTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(BundleList::class)
            ->assertStatus(200);
    }
}

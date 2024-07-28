<?php

namespace Tests\Feature\Livewire\Product;

use FluxErp\Livewire\Product\VariantList;
use Livewire\Livewire;
use Tests\TestCase;

class VariantListTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(VariantList::class)
            ->assertStatus(200);
    }
}

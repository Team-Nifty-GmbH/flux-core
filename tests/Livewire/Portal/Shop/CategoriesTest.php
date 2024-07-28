<?php

namespace Tests\Feature\Livewire\Portal\Shop;

use FluxErp\Livewire\Portal\Shop\Categories;
use Livewire\Livewire;
use Tests\TestCase;

class CategoriesTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(Categories::class)
            ->assertStatus(200);
    }
}

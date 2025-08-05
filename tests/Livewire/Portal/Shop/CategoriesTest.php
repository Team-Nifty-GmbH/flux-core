<?php

namespace FluxErp\Tests\Livewire\Portal\Shop;

use FluxErp\Livewire\Portal\Shop\Categories;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class CategoriesTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::withoutLazyLoading()
            ->test(Categories::class)
            ->assertStatus(200);
    }
}

<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\Categories;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class CategoriesTest extends BaseSetup
{
    public function test_renders_successfully(): void
    {
        Livewire::test(Categories::class)
            ->assertStatus(200);
    }
}

<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\CategoryList;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class CategoryListTest extends BaseSetup
{
    public function test_renders_successfully(): void
    {
        Livewire::test(CategoryList::class)
            ->assertStatus(200);
    }
}

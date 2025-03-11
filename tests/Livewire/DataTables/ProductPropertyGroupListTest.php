<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\ProductPropertyGroupList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ProductPropertyGroupListTest extends TestCase
{
    protected string $livewireComponent = ProductPropertyGroupList::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

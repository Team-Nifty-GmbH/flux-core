<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\TagList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class TagListTest extends TestCase
{
    protected string $livewireComponent = TagList::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

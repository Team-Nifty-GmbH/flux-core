<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\RecordOriginList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class RecordOriginListTest extends TestCase
{
    protected string $livewireComponent = RecordOriginList::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\PrintJobList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class PrintJobListTest extends TestCase
{
    protected string $livewireComponent = PrintJobList::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\PrinterList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class PrinterListTest extends TestCase
{
    protected string $livewireComponent = PrinterList::class;

    public function test_renders_successfully()
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

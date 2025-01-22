<?php

namespace FluxErp\Tests\Livewire\DataTablesWidgets;

use FluxErp\Livewire\Widgets\TotalOrdersCount;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class TotalOrdersCountTest extends TestCase
{
    protected string $livewireComponent = TotalOrdersCount::class;

    public function test_renders_successfully()
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

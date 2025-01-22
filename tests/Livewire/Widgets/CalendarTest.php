<?php

namespace FluxErp\Tests\Livewire\DataTablesWidgets;

use FluxErp\Livewire\Widgets\Calendar;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class CalendarTest extends TestCase
{
    protected string $livewireComponent = Calendar::class;

    public function test_renders_successfully()
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

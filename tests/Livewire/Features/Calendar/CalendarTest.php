<?php

namespace FluxErp\Tests\Livewire\Features\Calendar;

use FluxErp\Livewire\Features\Calendar\Calendar;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class CalendarTest extends TestCase
{
    protected string $livewireComponent = Calendar::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

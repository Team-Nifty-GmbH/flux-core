<?php

namespace FluxErp\Tests\Livewire\Features\Calendar;

use FluxErp\Livewire\Features\Calendar\CalendarEvent;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class CalendarEventTest extends TestCase
{
    protected string $livewireComponent = CalendarEvent::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

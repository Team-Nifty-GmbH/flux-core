<?php

namespace FluxErp\Tests\Livewire\Features\Calendar;

use FluxErp\Livewire\Features\Calendar\CalendarEventEdit;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class CalendarEventEditTest extends TestCase
{
    protected string $livewireComponent = CalendarEventEdit::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

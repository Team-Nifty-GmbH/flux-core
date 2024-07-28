<?php

namespace Tests\Feature\Livewire\Features\Calendar;

use FluxErp\Livewire\Features\Calendar\FluxCalendar;
use Livewire\Livewire;
use Tests\TestCase;

class FluxCalendarTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(FluxCalendar::class)
            ->assertStatus(200);
    }
}

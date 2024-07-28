<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\CalendarEventForm;
use Livewire\Livewire;
use Tests\TestCase;

class CalendarEventFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(CalendarEventForm::class)
            ->assertStatus(200);
    }
}

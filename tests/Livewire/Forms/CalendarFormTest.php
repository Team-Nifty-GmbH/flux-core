<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\CalendarForm;
use Livewire\Livewire;
use Tests\TestCase;

class CalendarFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(CalendarForm::class)
            ->assertStatus(200);
    }
}

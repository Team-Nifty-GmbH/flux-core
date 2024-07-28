<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\ScheduleForm;
use Livewire\Livewire;
use Tests\TestCase;

class ScheduleFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(ScheduleForm::class)
            ->assertStatus(200);
    }
}

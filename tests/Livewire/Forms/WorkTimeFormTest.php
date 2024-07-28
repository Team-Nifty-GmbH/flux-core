<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\WorkTimeForm;
use Livewire\Livewire;
use Tests\TestCase;

class WorkTimeFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(WorkTimeForm::class)
            ->assertStatus(200);
    }
}

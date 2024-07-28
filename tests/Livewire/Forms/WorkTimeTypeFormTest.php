<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\WorkTimeTypeForm;
use Livewire\Livewire;
use Tests\TestCase;

class WorkTimeTypeFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(WorkTimeTypeForm::class)
            ->assertStatus(200);
    }
}

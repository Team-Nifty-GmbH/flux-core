<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\TaskForm;
use Livewire\Livewire;
use Tests\TestCase;

class TaskFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(TaskForm::class)
            ->assertStatus(200);
    }
}

<?php

namespace Tests\Feature\Livewire\Widgets;

use FluxErp\Livewire\Widgets\MyResponsibleTasks;
use Livewire\Livewire;
use Tests\TestCase;

class MyResponsibleTasksTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(MyResponsibleTasks::class)
            ->assertStatus(200);
    }
}

<?php

namespace Tests\Feature\Livewire\Widgets;

use FluxErp\Livewire\Widgets\MyTasks;
use Livewire\Livewire;
use Tests\TestCase;

class MyTasksTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(MyTasks::class)
            ->assertStatus(200);
    }
}

<?php

namespace FluxErp\Tests\Livewire\Widgets;

use FluxErp\Livewire\Widgets\MyTasks;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class MyTasksTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::actingAs($this->user)
            ->test(MyTasks::class)
            ->assertStatus(200);
    }
}

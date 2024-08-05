<?php

namespace FluxErp\Tests\Livewire\Widgets;

use FluxErp\Livewire\Widgets\MyResponsibleTasks;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class MyResponsibleTasksTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::actingAs($this->user)
            ->test(MyResponsibleTasks::class)
            ->assertStatus(200);
    }
}

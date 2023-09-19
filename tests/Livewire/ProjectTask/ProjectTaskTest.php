<?php

namespace FluxErp\Tests\Livewire\ProjectTask;

use FluxErp\Livewire\ProjectTask\ProjectTask;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ProjectTaskTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(ProjectTask::class)
            ->assertStatus(200);
    }
}

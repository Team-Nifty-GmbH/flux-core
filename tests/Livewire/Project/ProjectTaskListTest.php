<?php

namespace Tests\Feature\Livewire\Project;

use FluxErp\Livewire\Project\ProjectTaskList;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectTaskListTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(ProjectTaskList::class)
            ->assertStatus(200);
    }
}

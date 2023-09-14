<?php

namespace FluxErp\Tests\Livewire\ProjectTask;

use FluxErp\Livewire\ProjectTask\ProjectTask;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class ProjectTaskTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_renders_successfully()
    {
        Livewire::test(ProjectTask::class)
            ->assertStatus(200);
    }
}

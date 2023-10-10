<?php

namespace FluxErp\Tests\Livewire\ProjectTask;

use FluxErp\Livewire\ProjectTask\ProjectTask;
use FluxErp\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class ProjectTaskTest extends TestCase
{
    use DatabaseTransactions;

    public function test_renders_successfully()
    {
        Livewire::test(ProjectTask::class)
            ->assertStatus(200);
    }
}

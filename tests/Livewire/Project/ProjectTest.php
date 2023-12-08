<?php

namespace FluxErp\Tests\Livewire\Project;

use FluxErp\Livewire\Project\Project as ProjectView;
use FluxErp\Models\Project;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class ProjectTest extends BaseSetup
{
    use DatabaseTransactions;

    private Project $project;

    public function setUp(): void
    {
        parent::setUp();

        $this->project = Project::factory()->create();
    }

    public function test_renders_successfully()
    {
        Livewire::test(ProjectView::class, ['id' => $this->project->id])
            ->assertStatus(200);
    }
}

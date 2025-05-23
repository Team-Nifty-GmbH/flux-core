<?php

namespace FluxErp\Tests\Livewire\Project;

use FluxErp\Livewire\Project\Project as ProjectView;
use FluxErp\Models\Project;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class ProjectTest extends BaseSetup
{
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->project = Project::factory()->create([
            'client_id' => $this->dbClient->getKey(),
        ]);
    }

    public function test_renders_successfully(): void
    {
        Livewire::test(ProjectView::class, ['id' => $this->project->id])
            ->assertStatus(200);
    }

    public function test_switch_tabs(): void
    {
        Livewire::test(ProjectView::class, ['id' => $this->project->id])->cycleTabs();
    }
}

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

        $this->project = Project::factory()->create([
            'client_id' => $this->dbClient->id,
        ]);
    }

    public function test_renders_successfully()
    {
        Livewire::test(ProjectView::class, ['id' => $this->project->id])
            ->assertStatus(200);
    }

    public function test_switch_tabs()
    {
        $component = Livewire::test(ProjectView::class, ['id' => $this->project->id]);

        foreach (Livewire::new(ProjectView::class)->getTabs() as $tab) {
            $component
                ->set('tab', $tab->component)
                ->assertStatus(200);

            if ($tab->isLivewireComponent) {
                $component->assertSeeLivewire($tab->component);
            }
        }
    }
}

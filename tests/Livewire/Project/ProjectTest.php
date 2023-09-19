<?php

namespace FluxErp\Tests\Livewire\Project;

use FluxErp\Livewire\Project\Project as ProjectView;
use FluxErp\Models\Category;
use FluxErp\Models\Project;
use FluxErp\Models\ProjectTask;
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

        $category = Category::factory()->create([
            'model_type' => Project::class,
        ]);

        $categories = Category::factory()->count(2)->create([
                'model_type' => ProjectTask::class,
                'parent_id' => $category->id,
        ]);

        $this->project = Project::factory()->create([
            'category_id' => $category->id,
        ]);

        $this->project->categories()->attach($categories->pluck('id')->toArray());
    }

    public function test_renders_successfully()
    {
        Livewire::test(ProjectView::class, ['id' => $this->project->id])
            ->assertStatus(200);
    }
}

<?php

namespace FluxErp\Tests\Livewire\Project;

use FluxErp\Livewire\Project\Project;
use FluxErp\Models\Category;
use FluxErp\Models\ProjectTask;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class ProjectTest extends BaseSetup
{
    use DatabaseTransactions;

    private Collection $projects;

    private Model $category;

    private Collection $categories;

    public function setUp(): void
    {
        parent::setUp();

        $this->category = Category::factory()->create(['model_type' => \FluxErp\Models\Project::class]);
        $this->categories = Category::factory()
            ->count(2)
            ->create([
                'model_type' => ProjectTask::class,
                'parent_id' => $this->category->id,
            ]);

        $this->projects = \FluxErp\Models\Project::factory()->count(2)->create([
            'category_id' => $this->category->id,
        ]);

        $this->projects
            ->each(
                fn ($project) => $project->categories()->attach($this->categories->pluck('id')->toArray())
            );
    }

    public function test_renders_successfully()
    {
        Livewire::test(Project::class, ['id' => $this->projects->first()->id])
            ->assertStatus(200);
    }
}

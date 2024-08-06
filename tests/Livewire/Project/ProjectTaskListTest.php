<?php

namespace FluxErp\Tests\Livewire\Project;

use FluxErp\Livewire\Project\ProjectTaskList;
use FluxErp\Models\Client;
use FluxErp\Models\Project;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ProjectTaskListTest extends TestCase
{
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $client = Client::factory()->create([
            'is_default' => true,
        ]);
        $this->project = Project::factory()->create([
            'client_id' => $client->id,
        ]);
    }

    public function test_renders_successfully()
    {
        Livewire::test(ProjectTaskList::class, ['projectId' => $this->project->id])
            ->assertStatus(200);
    }
}

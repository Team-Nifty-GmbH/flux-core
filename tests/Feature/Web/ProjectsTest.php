<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;
use FluxErp\Models\Project;

class ProjectsTest extends BaseSetup
{
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->project = Project::factory()->create([
            'client_id' => $this->dbClient->getKey(),
        ]);
    }

    public function test_projects_list_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('projects.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/projects')
            ->assertStatus(200);
    }

    public function test_projects_list_no_user()
    {
        $this->get('/projects')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_projects_list_without_permission()
    {
        Permission::findOrCreate('projects.get', 'web');

        $this->actingAs($this->user, 'web')->get('/projects')
            ->assertStatus(403);
    }

    public function test_projects_id_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('projects.{id}.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/projects/' . $this->project->id)
            ->assertStatus(200);
    }

    public function test_projects_id_no_user()
    {
        $this->get('/projects/' . $this->project->id)
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_projects_id_without_permission()
    {
        Permission::findOrCreate('projects.{id}.get', 'web');

        $this->actingAs($this->user, 'web')->get('/projects/' . $this->project->id)
            ->assertStatus(403);
    }

    public function test_projects_id_project_not_found()
    {
        $this->project->delete();

        $this->user->givePermissionTo(Permission::findOrCreate('projects.{id}.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/projects/' . $this->project->id)
            ->assertStatus(404);
    }
}

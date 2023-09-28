<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ProjectsProjectTasksTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_projects_project_tasks_page()
    {
        // Todo: add route permission
        $this->user->assignRole('Super Admin');

        $this->actingAs($this->user, 'web')->get('/projects/project-tasks')
            ->assertStatus(200);
    }

    public function test_projects_project_tasks_no_user()
    {
        $this->get('/projects/project-tasks')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_projects_project_tasks_without_permission()
    {
        $this->actingAs($this->user, 'web')->get('/projects/project-tasks')
            ->assertStatus(403);
    }
}

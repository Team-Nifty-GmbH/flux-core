<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ProjectsTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_projects_no_user()
    {
        $this->get('/projects')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_projects_redirect_dashboard()
    {
        $this->user->givePermissionTo(Permission::findByName('projects.get', 'web'));

        $this->actingAs($this->user, guard: 'web')->get('/projects')
            ->assertStatus(301)
            ->assertRedirect(route('dashboard'));
    }

    public function test_projects_id_no_user()
    {
        $id = 1;

        $this->get('/projects/$id')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_projects_id_redirect_dashboard()
    {
        $id = 1;

        $this->user->givePermissionTo(Permission::findByName('projects.{id}.get', 'web'));

        $this->actingAs($this->user, guard: 'web')->get('/projects/$id')
            ->assertStatus(301)
            ->assertRedirect(route('dashboard'));
    }
}

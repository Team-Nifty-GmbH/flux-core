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
}

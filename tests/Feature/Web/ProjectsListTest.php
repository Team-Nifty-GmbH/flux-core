<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ProjectsListTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_projects_list_page()
    {
        // Todo: add route permission
        $this->user->assignRole('Super Admin');

        $this->actingAs($this->user, 'web')->get('/projects/list')
            ->assertStatus(200);
    }

    public function test_projects_list_no_user()
    {
        $this->get('/projects/list')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_projects_list_without_permission()
    {
        $this->actingAs($this->user, 'web')->get('/projects/list')
            ->assertStatus(403);
    }
}

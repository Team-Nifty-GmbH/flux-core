<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Models\Permission;
use FluxErp\Models\Task;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TasksTest extends BaseSetup
{
    use DatabaseTransactions;

    private Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->task = Task::factory()->create();
    }

    public function test_tasks_list_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('tasks.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/tasks')
            ->assertStatus(200);
    }

    public function test_tasks_list_no_user()
    {
        $this->get('/tasks')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_tasks_list_without_permission()
    {
        Permission::findOrCreate('tasks.get', 'web');

        $this->actingAs($this->user, 'web')->get('/tasks')
            ->assertStatus(403);
    }

    public function test_tasks_id_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('tasks.{id}.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/tasks/' . $this->task->id)
            ->assertStatus(200);
    }

    public function test_tasks_id_no_user()
    {
        $this->get('/tasks/' . $this->task->id)
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_tasks_id_without_permission()
    {
        Permission::findOrCreate('tasks.{id}.get', 'web');

        $this->actingAs($this->user, 'web')->get('/tasks/' . $this->task->id)
            ->assertStatus(403);
    }

    public function test_tasks_id_task_not_found()
    {
        $this->task->delete();

        $this->user->givePermissionTo(Permission::findOrCreate('tasks.{id}.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/tasks/' . $this->task->id)
            ->assertStatus(404);
    }
}

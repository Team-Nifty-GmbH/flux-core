<?php

namespace FluxErp\Tests\Livewire\Widgets;

use FluxErp\Livewire\Widgets\MyTasks;
use FluxErp\Models\Task;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Support\Str;
use Livewire\Livewire;

class MyTasksTest extends BaseSetup
{
    public function test_can_open_work_time_modal(): void
    {
        $task = Task::factory()->create([
            'name' => Str::uuid(),
        ]);
        $task->users()->attach($this->user);

        $component = Livewire::actingAs($this->user)
            ->test(MyTasks::class)
            ->assertSee($task->name);

        $this->assertMatchesRegularExpression(
            '/\$dispatch\(\s*[\'"]start-time-tracking[\'"]/',
            $component->html()
        );
    }

    public function test_renders_successfully(): void
    {
        Livewire::actingAs($this->user)
            ->test(MyTasks::class)
            ->assertStatus(200);
    }
}

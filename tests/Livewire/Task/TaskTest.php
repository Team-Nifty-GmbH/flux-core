<?php

namespace FluxErp\Tests\Livewire\Task;

use FluxErp\Livewire\Task\Task as TaskView;
use FluxErp\Models\Task;
use FluxErp\Tests\Livewire\BaseSetup;
use FluxErp\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class TaskTest extends BaseSetup
{
    use DatabaseTransactions;

    private Task $task;

    public function setUp(): void
    {
        parent::setUp();

        $this->task = Task::factory()->create();
    }

    public function test_renders_successfully()
    {
        Livewire::test(TaskView::class, ['id' => $this->task->id])
            ->assertStatus(200);
    }

    public function test_switch_tabs()
    {
        $component = Livewire::actingAs($this->user)
            ->test(TaskView::class, ['id' => $this->task->id]);

        foreach (Livewire::new(TaskView::class)->getTabs() as $tab) {
            $component
                ->set('taskTab', $tab->component)
                ->assertStatus(200);

            if ($tab->isLivewireComponent) {
                $component->assertSeeLivewire($tab->component);
            }
        }
    }
}

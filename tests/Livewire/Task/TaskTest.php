<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Livewire\Task\Task as TaskView;
use FluxErp\Models\Task;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->task = Task::factory()->create();
});

test('renders successfully', function (): void {
    Livewire::test(TaskView::class, ['id' => $this->task->id])
        ->assertStatus(200);
});

test('switch tabs', function (): void {
    Livewire::actingAs($this->user)
        ->test(TaskView::class, ['id' => $this->task->id])
        ->cycleTabs('taskTab');
});

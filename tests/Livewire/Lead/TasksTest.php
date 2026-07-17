<?php

use FluxErp\Livewire\Lead\Tasks;
use FluxErp\Models\Lead;
use FluxErp\Models\Task;
use Illuminate\Support\Str;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $lead = Lead::factory()->create();

    Livewire::test(Tasks::class, ['modelId' => $lead->getKey()])
        ->assertOk();
});

test('edit resets form and opens modal', function (): void {
    $lead = Lead::factory()->create();

    Livewire::test(Tasks::class, ['modelId' => $lead->getKey()])
        ->call('edit')
        ->assertSet('task.id', null)
        ->assertSet('task.name', null)
        ->assertSet('task.responsible_user_id', $this->user->getKey())
        ->assertSet('task.users', [$this->user->getKey()])
        ->assertExecutesJs("\$tsui.open.modal('new-task-modal');");
});

test('can create a task for a lead', function (): void {
    $lead = Lead::factory()->create();
    $name = Str::uuid()->toString();

    Livewire::test(Tasks::class, ['modelId' => $lead->getKey()])
        ->call('edit')
        ->set('task.name', $name)
        ->call('save')
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('tasks', [
        'name' => $name,
        'model_type' => morph_alias(Lead::class),
        'model_id' => $lead->getKey(),
        'responsible_user_id' => $this->user->getKey(),
    ]);
});

test('save validation fails without name', function (): void {
    $lead = Lead::factory()->create();

    Livewire::test(Tasks::class, ['modelId' => $lead->getKey()])
        ->call('edit')
        ->call('save')
        ->assertReturned(false);
});

test('task is associated with lead model', function (): void {
    $lead = Lead::factory()->create();
    $name = Str::uuid()->toString();

    Livewire::test(Tasks::class, ['modelId' => $lead->getKey()])
        ->call('edit')
        ->set('task.name', $name)
        ->call('save')
        ->assertReturned(true);

    $task = Task::query()->where('name', $name)->first();

    expect($task)->not->toBeNull()
        ->and($task->model_type)->toBe(morph_alias(Lead::class))
        ->and($task->model_id)->toBe($lead->getKey());
});

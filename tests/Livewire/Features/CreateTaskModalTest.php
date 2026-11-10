<?php

use FluxErp\Livewire\Features\CreateTaskModal;
use Illuminate\Support\Str;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(CreateTaskModal::class)
        ->assertOk();
});

test('can create a task', function (): void {
    $name = Str::uuid()->toString();

    Livewire::test(CreateTaskModal::class)
        ->call('resetTask')
        ->set('task.name', $name)
        ->call('save')
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('tasks', [
        'name' => $name,
        'responsible_user_id' => $this->user->getKey(),
        'state' => 'open',
    ]);
});

test('save validation fails without name', function (): void {
    Livewire::test(CreateTaskModal::class)
        ->call('save')
        ->assertReturned(false);
});

test('resetTask resets the form', function (): void {
    Livewire::test(CreateTaskModal::class)
        ->set('task.name', 'Some Task')
        ->set('task.description', 'Some description')
        ->call('resetTask')
        ->assertSet('task.name', null)
        ->assertSet('task.description', null)
        ->assertSet('task.responsible_user_id', $this->user->getKey())
        ->assertSet('task.users', [$this->user->getKey()]);
});

test('save sets model type and model id when modelType is set', function (): void {
    $project = FluxErp\Models\Project::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $name = Str::uuid()->toString();

    Livewire::test(CreateTaskModal::class, [
        'modelType' => morph_alias(FluxErp\Models\Project::class),
        'modelId' => $project->getKey(),
    ])
        ->call('resetTask')
        ->set('task.name', $name)
        ->call('save')
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('tasks', [
        'name' => $name,
        'model_type' => morph_alias(FluxErp\Models\Project::class),
        'model_id' => $project->getKey(),
    ]);
});

test('save clears model type and model id when modelType is null', function (): void {
    $name = Str::uuid()->toString();

    Livewire::test(CreateTaskModal::class)
        ->set('task.name', $name)
        ->call('save')
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('tasks', [
        'name' => $name,
        'model_type' => null,
        'model_id' => null,
    ]);
});

test('form resets after successful save', function (): void {
    Livewire::test(CreateTaskModal::class)
        ->set('task.name', 'Test Task')
        ->call('save')
        ->assertReturned(true)
        ->assertSet('task.name', null);
});

test('does not accept a linked record from the client', function (string $property, int|string $value): void {
    Livewire::test(CreateTaskModal::class)
        ->set($property, $value);
})
    ->with([
        ['modelId', 1],
        ['modelType', 'contact'],
    ])
    ->throws(CannotUpdateLockedPropertyException::class);

test('renders the due reminder fields', function (): void {
    Livewire::test(CreateTaskModal::class)
        ->assertSeeHtml('x-show="$wire.task.due_date"')
        ->assertSeeHtml('wire:model="task.has_due_reminder"')
        ->assertSeeHtml('wire:model="task.due_reminder_minutes_before"');
});

test('can create a task with a due reminder', function (): void {
    $name = Str::uuid()->toString();

    Livewire::test(CreateTaskModal::class)
        ->call('resetTask')
        ->set('task.name', $name)
        ->set('task.due_date', now()->addDay()->toDateString())
        ->set('task.has_due_reminder', true)
        ->set('task.due_reminder_minutes_before', 30)
        ->call('save')
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('tasks', [
        'name' => $name,
        'has_due_reminder' => true,
        'due_reminder_minutes_before' => 30,
    ]);
});

<?php

use FluxErp\Livewire\Features\CreateTaskModal;
use Illuminate\Support\Str;
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

    Livewire::test(CreateTaskModal::class)
        ->set('modelType', morph_alias(FluxErp\Models\Project::class))
        ->set('modelId', $project->getKey())
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
        ->set('modelType', null)
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

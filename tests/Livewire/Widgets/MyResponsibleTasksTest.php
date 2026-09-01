<?php

use FluxErp\Livewire\Task\TaskList;
use FluxErp\Livewire\Widgets\MyResponsibleTasks;
use FluxErp\Models\Task;
use Illuminate\Support\Str;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

test('renders successfully', function (): void {
    Livewire::actingAs($this->user)
        ->test(MyResponsibleTasks::class)
        ->assertOk();
});

test('renders show dropdown option', function (): void {
    Livewire::actingAs($this->user)
        ->test(MyResponsibleTasks::class)
        ->assertSee(__('Show'));
});

test('show in task list redirects to tasks route', function (): void {
    Livewire::actingAs($this->user)
        ->test(MyResponsibleTasks::class)
        ->call('showInTaskList')
        ->assertRedirect(route('tasks'));
});

test('show in task list creates session filter scoped to responsible tasks', function (): void {
    $responsibleTask = Task::factory()->create([
        'name' => Str::uuid(),
        'state' => 'open',
        'responsible_user_id' => $this->user->getKey(),
    ]);

    $foreignTask = Task::factory()->create([
        'name' => Str::uuid(),
        'state' => 'open',
    ]);

    Livewire::actingAs($this->user)
        ->test(MyResponsibleTasks::class)
        ->call('showInTaskList');

    $cacheKey = Livewire::new(TaskList::class)->getCacheKey();
    $sessionFilter = SessionFilter::retrieve($cacheKey);

    expect($sessionFilter)->toBeInstanceOf(SessionFilter::class);

    $query = Task::query();
    ($sessionFilter->getClosure())($query);
    $filtered = $query->get();

    expect($filtered->contains($responsibleTask->getKey()))->toBeTrue()
        ->and($filtered->contains($foreignTask->getKey()))->toBeFalse();
});

test('show in task list excludes end state tasks', function (): void {
    $openTask = Task::factory()->create([
        'name' => Str::uuid(),
        'state' => 'open',
        'responsible_user_id' => $this->user->getKey(),
    ]);

    $doneTask = Task::factory()->create([
        'name' => Str::uuid(),
        'state' => 'done',
        'responsible_user_id' => $this->user->getKey(),
    ]);

    Livewire::actingAs($this->user)
        ->test(MyResponsibleTasks::class)
        ->call('showInTaskList');

    $cacheKey = Livewire::new(TaskList::class)->getCacheKey();
    $query = Task::query();
    (SessionFilter::retrieve($cacheKey)->getClosure())($query);
    $filtered = $query->get();

    expect($filtered->contains($openTask->getKey()))->toBeTrue()
        ->and($filtered->contains($doneTask->getKey()))->toBeFalse();
});

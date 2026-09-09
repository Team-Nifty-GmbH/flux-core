<?php

use FluxErp\Livewire\Task\TaskList;
use FluxErp\Livewire\Widgets\MyTasks;
use FluxErp\Models\Task;
use Illuminate\Support\Str;
use Livewire\Livewire;
use TeamNiftyGmbH\DataTable\Helpers\SessionFilter;

test('can open work time modal', function (): void {
    $task = Task::factory()->create([
        'name' => Str::uuid(),
        'state' => 'open',
    ]);
    $task->users()->attach($this->user);

    $component = Livewire::actingAs($this->user)
        ->test(MyTasks::class)
        ->assertSee($task->name);

    expect($component->html())->toMatch('/\$dispatch\(\s*[\'"]start-time-tracking[\'"]/');
});

test('renders successfully', function (): void {
    Livewire::actingAs($this->user)
        ->test(MyTasks::class)
        ->assertOk();
});

test('renders show dropdown option', function (): void {
    Livewire::actingAs($this->user)
        ->test(MyTasks::class)
        ->assertSee(__('Show'));
});

test('show in task list redirects to tasks route', function (): void {
    Livewire::actingAs($this->user)
        ->test(MyTasks::class)
        ->call('showInTaskList')
        ->assertRedirect(route('tasks'));
});

test('show in task list creates session filter scoped to own tasks', function (): void {
    $ownTask = Task::factory()->create([
        'name' => Str::uuid(),
        'state' => 'open',
    ]);
    $ownTask->users()->attach($this->user);

    $foreignTask = Task::factory()->create([
        'name' => Str::uuid(),
        'state' => 'open',
    ]);

    $responsibleOnlyTask = Task::factory()->create([
        'name' => Str::uuid(),
        'state' => 'open',
        'responsible_user_id' => $this->user->getKey(),
    ]);

    Livewire::actingAs($this->user)
        ->test(MyTasks::class)
        ->call('showInTaskList');

    $cacheKey = Livewire::new(TaskList::class)->getCacheKey();
    $sessionFilter = SessionFilter::retrieve($cacheKey);

    expect($sessionFilter)->toBeInstanceOf(SessionFilter::class);

    $query = Task::query();
    ($sessionFilter->getClosure())($query);
    $filtered = $query->get();

    expect($filtered->contains($ownTask->getKey()))->toBeTrue()
        ->and($filtered->contains($foreignTask->getKey()))->toBeFalse()
        ->and($filtered->contains($responsibleOnlyTask->getKey()))->toBeFalse();
});

test('show in task list excludes end state tasks', function (): void {
    $openTask = Task::factory()->create([
        'name' => Str::uuid(),
        'state' => 'open',
    ]);
    $openTask->users()->attach($this->user);

    $doneTask = Task::factory()->create([
        'name' => Str::uuid(),
        'state' => 'done',
    ]);
    $doneTask->users()->attach($this->user);

    Livewire::actingAs($this->user)
        ->test(MyTasks::class)
        ->call('showInTaskList');

    $cacheKey = Livewire::new(TaskList::class)->getCacheKey();
    $query = Task::query();
    (SessionFilter::retrieve($cacheKey)->getClosure())($query);
    $filtered = $query->get();

    expect($filtered->contains($openTask->getKey()))->toBeTrue()
        ->and($filtered->contains($doneTask->getKey()))->toBeFalse();
});

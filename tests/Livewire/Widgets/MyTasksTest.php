<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Livewire\Widgets\MyTasks;
use FluxErp\Models\Task;
use Illuminate\Support\Str;
use Livewire\Livewire;

test('can open work time modal', function (): void {
    $task = Task::factory()->create([
        'name' => Str::uuid(),
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
        ->assertStatus(200);
});

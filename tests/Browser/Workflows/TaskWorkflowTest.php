<?php

use FluxErp\Models\Task;

beforeEach(function (): void {
    $this->task = Task::factory()->create([
        'name' => 'Browser Test Task',
    ]);
});

test('task list loads without js errors', function (): void {
    visit(route('tasks'))
        ->assertRoute('tasks')
        ->assertNoSmoke()
        ->assertNoJavascriptErrors();
});

test('task list shows data table', function (): void {
    visit(route('tasks'))
        ->assertRoute('tasks')
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

test('task detail page loads', function (): void {
    visit(route('tasks.id', ['id' => $this->task->getKey()]))
        ->assertNoSmoke()
        ->assertNoJavascriptErrors();
});

test('task detail shows task name', function (): void {
    visit(route('tasks.id', ['id' => $this->task->getKey()]))
        ->assertNoSmoke()
        ->assertSee('Browser Test Task');
});

test('task detail tabs switch without errors', function (): void {
    $page = visit(route('tasks.id', ['id' => $this->task->getKey()]))
        ->assertNoSmoke();

    $page->script(<<<'JS'
        () => {
            const tabs = document.querySelectorAll('[wire\\:click*="tab"]');
            if (tabs.length > 1) tabs[1].click();
        }
    JS);

    $page->assertNoJavascriptErrors();

    $page->script(<<<'JS'
        () => {
            const tabs = document.querySelectorAll('[wire\\:click*="tab"]');
            if (tabs.length > 2) tabs[2].click();
        }
    JS);

    $page->assertNoJavascriptErrors();
});

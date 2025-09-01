<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\DataTables\TaskList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TaskList::class)
        ->assertStatus(200);
});

<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\TaskList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class TaskListTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(TaskList::class)
            ->assertStatus(200);
    }
}

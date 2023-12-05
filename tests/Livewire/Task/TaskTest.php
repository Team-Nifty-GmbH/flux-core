<?php

namespace FluxErp\Tests\Livewire\Task;

use FluxErp\Livewire\Task\Task;
use FluxErp\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class TaskTest extends TestCase
{
    use DatabaseTransactions;

    public function test_renders_successfully()
    {
        Livewire::test(Task::class)
            ->assertStatus(200);
    }
}

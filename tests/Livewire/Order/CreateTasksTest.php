<?php

namespace Tests\Feature\Livewire\Order;

use FluxErp\Livewire\Order\CreateTasks;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class CreateTasksTest extends TestCase
{
    protected string $livewireComponent = CreateTasks::class;

    public function test_renders_successfully()
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

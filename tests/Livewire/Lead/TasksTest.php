<?php

namespace FluxErp\Tests\Livewire\Lead;

use FluxErp\Livewire\Lead\Tasks;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class TasksTest extends TestCase
{
    protected string $livewireComponent = Tasks::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

<?php

namespace FluxErp\Tests\Livewire\Widgets\Project;

use FluxErp\Livewire\Widgets\Project\TasksByState;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class TasksByStateTest extends TestCase
{
    protected string $livewireComponent = TasksByState::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

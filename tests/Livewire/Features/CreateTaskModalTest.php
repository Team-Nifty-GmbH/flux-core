<?php

namespace FluxErp\Tests\Livewire\Features;

use FluxErp\Livewire\Features\CreateTaskModal;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class CreateTaskModalTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(CreateTaskModal::class)
            ->assertStatus(200);
    }
}

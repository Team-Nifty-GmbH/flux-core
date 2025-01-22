<?php

namespace FluxErp\Tests\Livewire\DataTablesOrder;

use FluxErp\Livewire\Order\Projects;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ProjectsTest extends TestCase
{
    protected string $livewireComponent = Projects::class;

    public function test_renders_successfully()
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

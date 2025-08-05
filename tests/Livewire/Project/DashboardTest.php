<?php

namespace FluxErp\Tests\Livewire\Project;

use FluxErp\Livewire\Project\Dashboard;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class DashboardTest extends TestCase
{
    protected string $livewireComponent = Dashboard::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

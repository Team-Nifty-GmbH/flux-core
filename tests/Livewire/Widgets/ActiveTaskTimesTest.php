<?php

namespace Tests\Feature\Livewire\Widgets;

use FluxErp\Livewire\Widgets\ActiveTaskTimes;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ActiveTaskTimesTest extends TestCase
{
    protected string $livewireComponent = ActiveTaskTimes::class;

    public function test_renders_successfully()
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

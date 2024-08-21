<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\Scheduling;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class SchedulingTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(Scheduling::class)
            ->assertStatus(200);
    }
}

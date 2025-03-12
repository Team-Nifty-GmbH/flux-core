<?php

namespace FluxErp\Tests\Livewire\Widgets;

use FluxErp\Livewire\Widgets\WorkTimes;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class WorkTimesTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(WorkTimes::class)
            ->assertStatus(200);
    }
}

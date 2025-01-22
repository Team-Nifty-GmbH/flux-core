<?php

namespace FluxErp\Tests\Livewire\Widgets;

use FluxErp\Livewire\Widgets\ActiveDailyWorkTimes;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ActiveDailyWorkTimesTest extends TestCase
{
    protected string $livewireComponent = ActiveDailyWorkTimes::class;

    public function test_renders_successfully()
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

<?php

namespace Tests\Feature\Livewire\Widgets;

use FluxErp\Livewire\Widgets\MyWorkTimes;
use Livewire\Livewire;
use Tests\TestCase;

class MyWorkTimesTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(MyWorkTimes::class)
            ->assertStatus(200);
    }
}

<?php

namespace FluxErp\Tests\Livewire\Widgets;

use FluxErp\Livewire\Widgets\MyWorkTimes;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class MyWorkTimesTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(MyWorkTimes::class)
            ->assertStatus(200);
    }
}

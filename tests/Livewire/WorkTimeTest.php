<?php

namespace Tests\Feature\Livewire;

use FluxErp\Livewire\WorkTime;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class WorkTimeTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(WorkTime::class)
            ->assertStatus(200);
    }
}

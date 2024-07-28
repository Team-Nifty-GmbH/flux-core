<?php

namespace Tests\Feature\Livewire;

use FluxErp\Livewire\WorkTime;
use Livewire\Livewire;
use Tests\TestCase;

class WorkTimeTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(WorkTime::class)
            ->assertStatus(200);
    }
}

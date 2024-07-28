<?php

namespace Tests\Feature\Livewire\Settings;

use FluxErp\Livewire\Settings\Scheduling;
use Livewire\Livewire;
use Tests\TestCase;

class SchedulingTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(Scheduling::class)
            ->assertStatus(200);
    }
}

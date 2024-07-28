<?php

namespace Tests\Feature\Livewire\Contact;

use FluxErp\Livewire\Contact\WorkTimes;
use Livewire\Livewire;
use Tests\TestCase;

class WorkTimesTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(WorkTimes::class)
            ->assertStatus(200);
    }
}

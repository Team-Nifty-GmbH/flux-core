<?php

namespace FluxErp\Tests\Livewire\Contact;

use FluxErp\Livewire\Contact\WorkTimes;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class WorkTimesTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(WorkTimes::class)
            ->assertStatus(200);
    }
}

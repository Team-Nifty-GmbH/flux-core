<?php

namespace Tests\Feature\Livewire\Settings;

use FluxErp\Livewire\Settings\QueueMonitor;
use Livewire\Livewire;
use Tests\TestCase;

class QueueMonitorTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(QueueMonitor::class)
            ->assertStatus(200);
    }
}

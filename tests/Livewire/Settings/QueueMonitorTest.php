<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\QueueMonitor;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class QueueMonitorTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(QueueMonitor::class)
            ->assertStatus(200);
    }
}

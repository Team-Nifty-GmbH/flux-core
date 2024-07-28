<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\ActivityLogs;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ActivityLogsTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(ActivityLogs::class)
            ->assertStatus(200);
    }
}

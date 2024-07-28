<?php

namespace Tests\Feature\Livewire\Settings;

use FluxErp\Livewire\Settings\ActivityLogs;
use Livewire\Livewire;
use Tests\TestCase;

class ActivityLogsTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(ActivityLogs::class)
            ->assertStatus(200);
    }
}

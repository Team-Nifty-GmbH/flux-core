<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\Logs;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class LogsTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::test(Logs::class)
            ->assertStatus(200);
    }
}

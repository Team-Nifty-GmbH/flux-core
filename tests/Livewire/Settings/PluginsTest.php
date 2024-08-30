<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\Plugins;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class PluginsTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(Plugins::class)
            ->assertStatus(200);
    }
}

<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\Settings;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class SettingsTest extends TestCase
{
    protected string $livewireComponent = Settings::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

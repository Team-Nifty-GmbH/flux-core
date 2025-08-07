<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\Locations;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class LocationsTest extends BaseSetup
{
    protected string $livewireComponent = Locations::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
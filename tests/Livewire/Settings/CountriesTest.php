<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\Countries;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class CountriesTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::test(Countries::class)
            ->assertStatus(200);
    }
}

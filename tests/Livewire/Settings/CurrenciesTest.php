<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\Currencies;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class CurrenciesTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::test(Currencies::class)
            ->assertStatus(200);
    }
}

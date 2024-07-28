<?php

namespace FluxErp\Tests\Livewire\Features;

use FluxErp\Livewire\Features\CommissionRates;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class CommissionRatesTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::test(CommissionRates::class, ['userId' => $this->user->id])
            ->assertStatus(200);
    }
}

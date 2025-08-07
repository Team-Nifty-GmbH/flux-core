<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\VacationCarryoverRules;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class VacationCarryoverRulesTest extends BaseSetup
{
    protected string $livewireComponent = VacationCarryoverRules::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
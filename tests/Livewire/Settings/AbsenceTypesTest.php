<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\AbsenceTypes;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class AbsenceTypesTest extends BaseSetup
{
    protected string $livewireComponent = AbsenceTypes::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
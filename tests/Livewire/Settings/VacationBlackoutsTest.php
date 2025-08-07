<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\VacationBlackouts;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class VacationBlackoutsTest extends BaseSetup
{
    protected string $livewireComponent = VacationBlackouts::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
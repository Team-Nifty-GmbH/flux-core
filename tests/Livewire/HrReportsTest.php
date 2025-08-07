<?php

namespace FluxErp\Tests\Livewire;

use FluxErp\Livewire\HumanResources\HrReports;
use Livewire\Livewire;

class HrReportsTest extends BaseSetup
{
    protected string $livewireComponent = HrReports::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

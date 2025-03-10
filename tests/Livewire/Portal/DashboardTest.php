<?php

namespace FluxErp\Tests\Livewire\Portal;

use FluxErp\Livewire\Portal\Dashboard;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class DashboardTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::test(Dashboard::class)
            ->assertStatus(200);
    }
}

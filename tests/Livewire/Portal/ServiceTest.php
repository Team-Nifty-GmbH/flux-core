<?php

namespace FluxErp\Tests\Livewire\Portal;

use FluxErp\Livewire\Portal\Service;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class ServiceTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_renders_successfully()
    {
        Livewire::test(Service::class)
            ->assertStatus(200);
    }
}

<?php

namespace FluxErp\Tests\Livewire\Portal;

use FluxErp\Livewire\Portal\Service;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class ServiceTest extends BaseSetup
{
    public function test_renders_successfully(): void
    {
        Livewire::test(Service::class)
            ->assertStatus(200);
    }
}

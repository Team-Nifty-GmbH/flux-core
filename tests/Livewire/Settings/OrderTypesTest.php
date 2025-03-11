<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\OrderTypes;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class OrderTypesTest extends BaseSetup
{
    public function test_renders_successfully(): void
    {
        Livewire::test(OrderTypes::class)
            ->assertStatus(200);
    }
}

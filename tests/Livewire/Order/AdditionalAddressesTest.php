<?php

namespace FluxErp\Tests\Livewire\Order;

use FluxErp\Livewire\Order\AdditionalAddresses;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class AdditionalAddressesTest extends TestCase
{
    protected string $livewireComponent = AdditionalAddresses::class;

    public function test_renders_successfully()
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

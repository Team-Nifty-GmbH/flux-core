<?php

namespace FluxErp\Tests\Livewire\Portal\Shop;

use FluxErp\Livewire\Portal\Shop\Checkout;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class CheckoutTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::actingAs($this->address)
            ->test(Checkout::class)
            ->assertStatus(200);
    }
}

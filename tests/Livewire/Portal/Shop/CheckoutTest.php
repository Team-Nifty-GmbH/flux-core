<?php

namespace Tests\Feature\Livewire\Portal\Shop;

use FluxErp\Livewire\Portal\Shop\Checkout;
use Livewire\Livewire;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(Checkout::class)
            ->assertStatus(200);
    }
}

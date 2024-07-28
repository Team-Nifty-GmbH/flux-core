<?php

namespace Tests\Feature\Livewire\Portal\Shop;

use FluxErp\Livewire\Portal\Shop\CheckoutFinish;
use Livewire\Livewire;
use Tests\TestCase;

class CheckoutFinishTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(CheckoutFinish::class)
            ->assertStatus(200);
    }
}

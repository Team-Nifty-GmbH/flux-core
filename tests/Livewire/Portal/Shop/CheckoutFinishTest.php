<?php

namespace FluxErp\Tests\Livewire\Portal\Shop;

use FluxErp\Livewire\Portal\Shop\CheckoutFinish;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class CheckoutFinishTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(CheckoutFinish::class)
            ->assertStatus(200);
    }
}

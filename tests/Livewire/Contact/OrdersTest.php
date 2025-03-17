<?php

namespace FluxErp\Tests\Livewire\Contact;

use FluxErp\Livewire\Contact\Orders;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class OrdersTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(Orders::class)
            ->assertStatus(200);
    }
}

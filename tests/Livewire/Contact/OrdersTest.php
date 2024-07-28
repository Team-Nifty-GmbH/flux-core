<?php

namespace Tests\Feature\Livewire\Contact;

use FluxErp\Livewire\Contact\Orders;
use Livewire\Livewire;
use Tests\TestCase;

class OrdersTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(Orders::class)
            ->assertStatus(200);
    }
}

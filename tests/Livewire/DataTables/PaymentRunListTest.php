<?php

namespace Tests\Feature\Livewire\DataTables;

use FluxErp\Livewire\DataTables\PaymentRunList;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentRunListTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(PaymentRunList::class)
            ->assertStatus(200);
    }
}

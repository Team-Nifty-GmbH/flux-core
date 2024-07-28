<?php

namespace Tests\Feature\Livewire\DataTables;

use FluxErp\Livewire\DataTables\PaymentTypeList;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentTypeListTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(PaymentTypeList::class)
            ->assertStatus(200);
    }
}

<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\PaymentRunList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class PaymentRunListTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(PaymentRunList::class)
            ->assertStatus(200);
    }
}

<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\PaymentTypeList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class PaymentTypeListTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(PaymentTypeList::class)
            ->assertStatus(200);
    }
}

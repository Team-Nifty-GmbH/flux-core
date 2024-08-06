<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\PaymentReminderList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class PaymentReminderListTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(PaymentReminderList::class)
            ->assertStatus(200);
    }
}

<?php

namespace Tests\Feature\Livewire\DataTables;

use FluxErp\Livewire\DataTables\PaymentReminderList;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentReminderListTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(PaymentReminderList::class)
            ->assertStatus(200);
    }
}

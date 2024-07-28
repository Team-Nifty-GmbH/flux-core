<?php

namespace Tests\Feature\Livewire\DataTables;

use FluxErp\Livewire\DataTables\PaymentReminderTextList;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentReminderTextListTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(PaymentReminderTextList::class)
            ->assertStatus(200);
    }
}

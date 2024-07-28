<?php

namespace Tests\Feature\Livewire\Accounting;

use FluxErp\Livewire\Accounting\PaymentReminder;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentReminderTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(PaymentReminder::class)
            ->assertStatus(200);
    }
}

<?php

namespace FluxErp\Tests\Livewire\Accounting;

use FluxErp\Livewire\Accounting\PaymentReminder;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class PaymentReminderTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(PaymentReminder::class)
            ->assertStatus(200);
    }
}

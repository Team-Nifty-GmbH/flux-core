<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\PaymentReminderForm;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentReminderFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(PaymentReminderForm::class)
            ->assertStatus(200);
    }
}

<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\PaymentReminderTextForm;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentReminderTextFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(PaymentReminderTextForm::class)
            ->assertStatus(200);
    }
}

<?php

namespace Tests\Feature\Livewire\Settings;

use FluxErp\Livewire\Settings\PaymentReminderTexts;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentReminderTextsTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(PaymentReminderTexts::class)
            ->assertStatus(200);
    }
}

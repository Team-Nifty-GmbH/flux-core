<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\PaymentReminderTexts;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class PaymentReminderTextsTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(PaymentReminderTexts::class)
            ->assertStatus(200);
    }
}

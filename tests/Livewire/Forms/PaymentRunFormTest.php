<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\PaymentRunForm;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentRunFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(PaymentRunForm::class)
            ->assertStatus(200);
    }
}

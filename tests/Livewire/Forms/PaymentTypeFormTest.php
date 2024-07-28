<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\PaymentTypeForm;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentTypeFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(PaymentTypeForm::class)
            ->assertStatus(200);
    }
}

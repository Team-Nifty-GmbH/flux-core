<?php

namespace Tests\Feature\Livewire\Settings;

use FluxErp\Livewire\Settings\PaymentTypes;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentTypesTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(PaymentTypes::class)
            ->assertStatus(200);
    }
}

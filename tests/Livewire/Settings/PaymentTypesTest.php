<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\PaymentTypes;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class PaymentTypesTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(PaymentTypes::class)
            ->assertStatus(200);
    }
}

<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\OrderForm;
use Livewire\Livewire;
use Tests\TestCase;

class OrderFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(OrderForm::class)
            ->assertStatus(200);
    }
}

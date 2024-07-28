<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\OrderTypeForm;
use Livewire\Livewire;
use Tests\TestCase;

class OrderTypeFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(OrderTypeForm::class)
            ->assertStatus(200);
    }
}

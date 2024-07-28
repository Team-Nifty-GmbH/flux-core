<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\OrderPositionForm;
use Livewire\Livewire;
use Tests\TestCase;

class OrderPositionFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(OrderPositionForm::class)
            ->assertStatus(200);
    }
}

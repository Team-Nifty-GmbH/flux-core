<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\OrderReplicateForm;
use Livewire\Livewire;
use Tests\TestCase;

class OrderReplicateFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(OrderReplicateForm::class)
            ->assertStatus(200);
    }
}

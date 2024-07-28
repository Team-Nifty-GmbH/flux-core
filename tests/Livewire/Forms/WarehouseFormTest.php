<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\WarehouseForm;
use Livewire\Livewire;
use Tests\TestCase;

class WarehouseFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(WarehouseForm::class)
            ->assertStatus(200);
    }
}

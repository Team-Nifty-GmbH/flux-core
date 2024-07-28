<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\UnitForm;
use Livewire\Livewire;
use Tests\TestCase;

class UnitFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(UnitForm::class)
            ->assertStatus(200);
    }
}

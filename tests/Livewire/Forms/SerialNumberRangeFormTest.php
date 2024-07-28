<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\SerialNumberRangeForm;
use Livewire\Livewire;
use Tests\TestCase;

class SerialNumberRangeFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(SerialNumberRangeForm::class)
            ->assertStatus(200);
    }
}

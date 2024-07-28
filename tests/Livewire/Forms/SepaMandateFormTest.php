<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\SepaMandateForm;
use Livewire\Livewire;
use Tests\TestCase;

class SepaMandateFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(SepaMandateForm::class)
            ->assertStatus(200);
    }
}

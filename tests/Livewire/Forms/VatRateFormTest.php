<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\VatRateForm;
use Livewire\Livewire;
use Tests\TestCase;

class VatRateFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(VatRateForm::class)
            ->assertStatus(200);
    }
}

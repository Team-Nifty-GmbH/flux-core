<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\PriceListForm;
use Livewire\Livewire;
use Tests\TestCase;

class PriceListFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(PriceListForm::class)
            ->assertStatus(200);
    }
}

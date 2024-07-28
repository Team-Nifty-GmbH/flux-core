<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\CurrencyForm;
use Livewire\Livewire;
use Tests\TestCase;

class CurrencyFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(CurrencyForm::class)
            ->assertStatus(200);
    }
}

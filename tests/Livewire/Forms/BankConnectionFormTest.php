<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\BankConnectionForm;
use Livewire\Livewire;
use Tests\TestCase;

class BankConnectionFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(BankConnectionForm::class)
            ->assertStatus(200);
    }
}

<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\LedgerAccountForm;
use Livewire\Livewire;
use Tests\TestCase;

class LedgerAccountFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(LedgerAccountForm::class)
            ->assertStatus(200);
    }
}

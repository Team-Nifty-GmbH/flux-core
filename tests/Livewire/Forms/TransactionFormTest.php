<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\TransactionForm;
use Livewire\Livewire;
use Tests\TestCase;

class TransactionFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(TransactionForm::class)
            ->assertStatus(200);
    }
}

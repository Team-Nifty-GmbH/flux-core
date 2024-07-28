<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\ContactBankConnectionForm;
use Livewire\Livewire;
use Tests\TestCase;

class ContactBankConnectionFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(ContactBankConnectionForm::class)
            ->assertStatus(200);
    }
}

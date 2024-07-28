<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\MailAccountForm;
use Livewire\Livewire;
use Tests\TestCase;

class MailAccountFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(MailAccountForm::class)
            ->assertStatus(200);
    }
}

<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\MailFolderForm;
use Livewire\Livewire;
use Tests\TestCase;

class MailFolderFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(MailFolderForm::class)
            ->assertStatus(200);
    }
}

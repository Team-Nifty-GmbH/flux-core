<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\CommunicationForm;
use Livewire\Livewire;
use Tests\TestCase;

class CommunicationFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(CommunicationForm::class)
            ->assertStatus(200);
    }
}

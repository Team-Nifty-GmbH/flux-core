<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\ClientForm;
use Livewire\Livewire;
use Tests\TestCase;

class ClientFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(ClientForm::class)
            ->assertStatus(200);
    }
}

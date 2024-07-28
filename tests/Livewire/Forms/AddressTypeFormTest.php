<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\AddressTypeForm;
use Livewire\Livewire;
use Tests\TestCase;

class AddressTypeFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(AddressTypeForm::class)
            ->assertStatus(200);
    }
}

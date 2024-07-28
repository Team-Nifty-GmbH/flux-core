<?php

namespace Tests\Feature\Livewire\Forms;

use FluxErp\Livewire\Forms\AddressForm;
use Livewire\Livewire;
use Tests\TestCase;

class AddressFormTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(AddressForm::class)
            ->assertStatus(200);
    }
}

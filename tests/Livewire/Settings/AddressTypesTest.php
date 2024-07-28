<?php

namespace Tests\Feature\Livewire\Settings;

use FluxErp\Livewire\Settings\AddressTypes;
use Livewire\Livewire;
use Tests\TestCase;

class AddressTypesTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(AddressTypes::class)
            ->assertStatus(200);
    }
}

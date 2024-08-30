<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\AddressTypes;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class AddressTypesTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(AddressTypes::class)
            ->assertStatus(200);
    }
}

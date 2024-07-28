<?php

namespace FluxErp\Tests\Livewire\Contact;

use FluxErp\Livewire\Contact\Addresses;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class AddressesTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(Addresses::class)
            ->assertStatus(200);
    }
}

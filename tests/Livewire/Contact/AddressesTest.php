<?php

namespace Tests\Feature\Livewire\Contact;

use FluxErp\Livewire\Contact\Addresses;
use Livewire\Livewire;
use Tests\TestCase;

class AddressesTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(Addresses::class)
            ->assertStatus(200);
    }
}

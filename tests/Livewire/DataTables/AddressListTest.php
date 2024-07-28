<?php

namespace Tests\Feature\Livewire\DataTables;

use FluxErp\Livewire\DataTables\AddressList;
use Livewire\Livewire;
use Tests\TestCase;

class AddressListTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(AddressList::class)
            ->assertStatus(200);
    }
}

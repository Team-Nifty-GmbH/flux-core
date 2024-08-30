<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\AddressList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class AddressListTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(AddressList::class)
            ->assertStatus(200);
    }
}

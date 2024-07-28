<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\AddressTypeList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class AddressTypeListTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(AddressTypeList::class)
            ->assertStatus(200);
    }
}

<?php

namespace Tests\Feature\Livewire\DataTables;

use FluxErp\Livewire\DataTables\AddressTypeList;
use Livewire\Livewire;
use Tests\TestCase;

class AddressTypeListTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(AddressTypeList::class)
            ->assertStatus(200);
    }
}

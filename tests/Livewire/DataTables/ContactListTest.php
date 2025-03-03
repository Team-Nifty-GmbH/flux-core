<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\AddressList;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class ContactListTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::test(AddressList::class)
            ->assertStatus(200);
    }
}

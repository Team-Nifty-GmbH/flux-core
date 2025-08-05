<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\ContactList;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class ContactListTest extends BaseSetup
{
    public function test_renders_successfully(): void
    {
        Livewire::test(ContactList::class)
            ->assertStatus(200);
    }
}

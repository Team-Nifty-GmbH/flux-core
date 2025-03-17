<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\ContactBankConnectionList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ContactBankConnectionListTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(ContactBankConnectionList::class)
            ->assertStatus(200);
    }
}

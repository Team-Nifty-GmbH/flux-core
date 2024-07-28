<?php

namespace Tests\Feature\Livewire\DataTables;

use FluxErp\Livewire\DataTables\ContactBankConnectionList;
use Livewire\Livewire;
use Tests\TestCase;

class ContactBankConnectionListTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(ContactBankConnectionList::class)
            ->assertStatus(200);
    }
}

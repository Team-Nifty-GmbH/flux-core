<?php

namespace Tests\Feature\Livewire\DataTables;

use FluxErp\Livewire\DataTables\BankConnectionList;
use Livewire\Livewire;
use Tests\TestCase;

class BankConnectionListTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(BankConnectionList::class)
            ->assertStatus(200);
    }
}

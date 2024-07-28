<?php

namespace Tests\Feature\Livewire\DataTables;

use FluxErp\Livewire\DataTables\LedgerAccountList;
use Livewire\Livewire;
use Tests\TestCase;

class LedgerAccountListTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(LedgerAccountList::class)
            ->assertStatus(200);
    }
}

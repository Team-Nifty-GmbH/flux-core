<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\LedgerAccountList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class LedgerAccountListTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(LedgerAccountList::class)
            ->assertStatus(200);
    }
}

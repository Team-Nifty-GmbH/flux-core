<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\TransactionList;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class TransactionListTest extends BaseSetup
{
    public function test_renders_successfully(): void
    {
        Livewire::test(TransactionList::class)
            ->assertStatus(200);
    }
}

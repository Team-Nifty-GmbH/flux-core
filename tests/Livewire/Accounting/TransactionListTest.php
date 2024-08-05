<?php

namespace FluxErp\Tests\Livewire\Accounting;

use FluxErp\Livewire\Accounting\TransactionList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class TransactionListTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(TransactionList::class)
            ->assertStatus(200);
    }
}

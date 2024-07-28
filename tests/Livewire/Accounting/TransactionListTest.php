<?php

namespace Tests\Feature\Livewire\Accounting;

use FluxErp\Livewire\Accounting\TransactionList;
use Livewire\Livewire;
use Tests\TestCase;

class TransactionListTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(TransactionList::class)
            ->assertStatus(200);
    }
}

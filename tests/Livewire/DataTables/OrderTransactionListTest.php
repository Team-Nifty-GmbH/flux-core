<?php

namespace FluxErp\Feature\Livewire\DataTables;

use FluxErp\Livewire\DataTables\OrderTransactionList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class OrderTransactionListTest extends TestCase
{
    protected string $livewireComponent = OrderTransactionList::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

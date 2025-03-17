<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\BankConnectionList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class BankConnectionListTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(BankConnectionList::class)
            ->assertStatus(200);
    }
}

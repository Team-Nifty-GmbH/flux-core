<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\CurrencyList;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class CurrencyListTest extends BaseSetup
{
    public function test_renders_successfully(): void
    {
        Livewire::test(CurrencyList::class)
            ->assertStatus(200);
    }
}

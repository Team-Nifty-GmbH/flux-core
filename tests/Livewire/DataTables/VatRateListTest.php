<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\VatRateList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class VatRateListTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(VatRateList::class)
            ->assertStatus(200);
    }
}

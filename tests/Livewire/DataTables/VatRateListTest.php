<?php

namespace Tests\Feature\Livewire\DataTables;

use FluxErp\Livewire\DataTables\VatRateList;
use Livewire\Livewire;
use Tests\TestCase;

class VatRateListTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(VatRateList::class)
            ->assertStatus(200);
    }
}

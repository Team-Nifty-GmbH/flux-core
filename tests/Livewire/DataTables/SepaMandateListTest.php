<?php

namespace Tests\Feature\Livewire\DataTables;

use FluxErp\Livewire\DataTables\SepaMandateList;
use Livewire\Livewire;
use Tests\TestCase;

class SepaMandateListTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(SepaMandateList::class)
            ->assertStatus(200);
    }
}

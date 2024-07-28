<?php

namespace Tests\Feature\Livewire\DataTables;

use FluxErp\Livewire\DataTables\CommissionList;
use Livewire\Livewire;
use Tests\TestCase;

class CommissionListTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(CommissionList::class)
            ->assertStatus(200);
    }
}

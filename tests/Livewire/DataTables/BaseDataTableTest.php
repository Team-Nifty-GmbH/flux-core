<?php

namespace Tests\Feature\Livewire\DataTables;

use FluxErp\Livewire\DataTables\BaseDataTable;
use Livewire\Livewire;
use Tests\TestCase;

class BaseDataTableTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(BaseDataTable::class)
            ->assertStatus(200);
    }
}

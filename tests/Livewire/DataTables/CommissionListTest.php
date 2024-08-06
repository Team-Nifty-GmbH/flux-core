<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\CommissionList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class CommissionListTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(CommissionList::class)
            ->assertStatus(200);
    }
}

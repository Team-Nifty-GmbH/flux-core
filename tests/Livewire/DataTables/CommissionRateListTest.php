<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\CommissionRateList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class CommissionRateListTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(CommissionRateList::class)
            ->assertStatus(200);
    }
}

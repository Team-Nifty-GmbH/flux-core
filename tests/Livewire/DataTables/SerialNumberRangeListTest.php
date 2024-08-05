<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\SerialNumberRangeList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class SerialNumberRangeListTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(SerialNumberRangeList::class)
            ->assertStatus(200);
    }
}

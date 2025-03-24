<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\WorkTimeTypeList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class WorkTimeTypeListTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(WorkTimeTypeList::class)
            ->assertStatus(200);
    }
}

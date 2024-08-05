<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\ActivityLogList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ActivityLogListTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(ActivityLogList::class)
            ->assertStatus(200);
    }
}

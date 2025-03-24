<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\FailedJobList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class FailedJobListTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(FailedJobList::class)
            ->assertStatus(200);
    }
}

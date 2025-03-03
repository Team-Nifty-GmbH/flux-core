<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\ProjectList;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class ProjectListTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::test(ProjectList::class)
            ->assertStatus(200);
    }
}

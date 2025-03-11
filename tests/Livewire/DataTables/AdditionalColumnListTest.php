<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\AdditionalColumnList;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class AdditionalColumnListTest extends BaseSetup
{
    public function test_renders_successfully(): void
    {
        Livewire::test(AdditionalColumnList::class)
            ->assertStatus(200);
    }
}

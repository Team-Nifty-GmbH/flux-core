<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\DiscountGroupList;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class DiscountGroupListTest extends BaseSetup
{
    public function test_renders_successfully(): void
    {
        Livewire::test(DiscountGroupList::class)
            ->assertStatus(200);
    }
}

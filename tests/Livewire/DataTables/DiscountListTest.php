<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\DiscountList;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class DiscountListTest extends BaseSetup
{
    public function test_renders_successfully(): void
    {
        Livewire::test(DiscountList::class)
            ->assertStatus(200);
    }
}

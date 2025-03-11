<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\CountryList;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class CountryListTest extends BaseSetup
{
    public function test_renders_successfully(): void
    {
        Livewire::test(CountryList::class)
            ->assertStatus(200);
    }
}

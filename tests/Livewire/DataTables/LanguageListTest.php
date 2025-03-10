<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\LanguageList;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class LanguageListTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::test(LanguageList::class)
            ->assertStatus(200);
    }
}

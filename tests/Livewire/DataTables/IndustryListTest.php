<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\IndustryList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class IndustryListTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(IndustryList::class)
            ->assertStatus(200);
    }
}

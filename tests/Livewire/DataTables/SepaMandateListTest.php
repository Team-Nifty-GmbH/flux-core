<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\SepaMandateList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class SepaMandateListTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(SepaMandateList::class)
            ->assertStatus(200);
    }
}

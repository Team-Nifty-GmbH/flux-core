<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\LeadStateList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class LeadStateListTest extends TestCase
{
    protected string $livewireComponent = LeadStateList::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

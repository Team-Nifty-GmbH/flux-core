<?php

namespace FluxErp\Tests\Livewire\Lead;

use FluxErp\Livewire\Lead\LeadList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class LeadListTest extends TestCase
{
    protected string $livewireComponent = LeadList::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

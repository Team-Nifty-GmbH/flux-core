<?php

namespace FluxErp\Tests\Livewire\Lead;

use FluxErp\Livewire\Lead\Lead;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class LeadTest extends TestCase
{
    protected string $livewireComponent = Lead::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

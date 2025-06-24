<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\LeadStates;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class LeadStatesTest extends TestCase
{
    protected string $livewireComponent = LeadStates::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

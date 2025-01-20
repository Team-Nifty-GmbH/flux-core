<?php

namespace Tests\Feature\Livewire\Widgets;

use FluxErp\Livewire\Widgets\UnassignedTickets;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class UnassignedTicketsTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(UnassignedTickets::class)
            ->assertStatus(200);
    }
}

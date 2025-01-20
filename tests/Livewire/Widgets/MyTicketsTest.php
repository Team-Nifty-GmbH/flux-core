<?php

namespace Tests\Feature\Livewire\Widgets;

use FluxErp\Livewire\Widgets\MyTickets;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class MyTicketsTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(MyTickets::class)
            ->assertStatus(200);
    }
}

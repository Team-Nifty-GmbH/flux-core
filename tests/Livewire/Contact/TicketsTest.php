<?php

namespace FluxErp\Tests\Livewire\Contact;

use FluxErp\Livewire\Contact\Tickets;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class TicketsTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(Tickets::class)
            ->assertStatus(200);
    }
}

<?php

namespace Tests\Feature\Livewire\Contact;

use FluxErp\Livewire\Contact\Tickets;
use Livewire\Livewire;
use Tests\TestCase;

class TicketsTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(Tickets::class)
            ->assertStatus(200);
    }
}

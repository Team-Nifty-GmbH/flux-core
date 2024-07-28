<?php

namespace Tests\Feature\Livewire\Ticket;

use FluxErp\Livewire\Ticket\Activities;
use Livewire\Livewire;
use Tests\TestCase;

class ActivitiesTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(Activities::class)
            ->assertStatus(200);
    }
}

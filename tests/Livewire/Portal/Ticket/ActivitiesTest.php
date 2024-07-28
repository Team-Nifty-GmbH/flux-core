<?php

namespace Tests\Feature\Livewire\Portal\Ticket;

use FluxErp\Livewire\Portal\Ticket\Activities;
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

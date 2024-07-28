<?php

namespace FluxErp\Tests\Livewire\Ticket;

use FluxErp\Livewire\Ticket\Activities;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ActivitiesTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(Activities::class)
            ->assertStatus(200);
    }
}

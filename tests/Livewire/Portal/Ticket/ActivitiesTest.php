<?php

namespace FluxErp\Tests\Livewire\Portal\Ticket;

use FluxErp\Livewire\Portal\Ticket\Activities;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ActivitiesTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(Activities::class)
            ->assertStatus(200);
    }
}

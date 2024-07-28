<?php

namespace Tests\Feature\Livewire\Order;

use FluxErp\Livewire\Order\Activities;
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

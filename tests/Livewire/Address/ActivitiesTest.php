<?php

namespace Tests\Feature\Livewire\Address;

use FluxErp\Livewire\Address\Activities;
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

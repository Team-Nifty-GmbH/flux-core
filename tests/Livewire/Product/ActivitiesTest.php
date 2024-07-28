<?php

namespace Tests\Feature\Livewire\Product;

use FluxErp\Livewire\Product\Activities;
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

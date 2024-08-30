<?php

namespace FluxErp\Tests\Livewire\Features;

use FluxErp\Livewire\Features\Activities;
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

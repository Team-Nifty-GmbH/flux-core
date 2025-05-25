<?php

namespace FluxErp\Tests\Livewire\Features;

use FluxErp\Support\Livewire\Activities;
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

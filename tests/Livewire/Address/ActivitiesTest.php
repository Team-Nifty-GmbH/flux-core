<?php

namespace FluxErp\Tests\Livewire\Address;

use FluxErp\Livewire\Address\Activities;
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

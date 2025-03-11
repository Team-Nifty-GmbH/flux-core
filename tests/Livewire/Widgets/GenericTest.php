<?php

namespace FluxErp\Tests\Livewire\Widgets;

use FluxErp\Livewire\Widgets\Generic;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class GenericTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(Generic::class)
            ->assertStatus(200);
    }
}

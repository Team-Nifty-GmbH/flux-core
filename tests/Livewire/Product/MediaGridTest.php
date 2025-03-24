<?php

namespace FluxErp\Tests\Livewire\Product;

use FluxErp\Livewire\Product\MediaGrid;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class MediaGridTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(MediaGrid::class)
            ->assertStatus(200);
    }
}

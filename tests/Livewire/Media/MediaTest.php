<?php

namespace FluxErp\Tests\Livewire\Media;

use FluxErp\Livewire\Media\Media;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class MediaTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(Media::class)
            ->assertStatus(200);
    }
}

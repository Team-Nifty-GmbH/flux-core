<?php

namespace Tests\Feature\Livewire\Media;

use FluxErp\Livewire\Media\Media;
use Livewire\Livewire;
use Tests\TestCase;

class MediaTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(Media::class)
            ->assertStatus(200);
    }
}

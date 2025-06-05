<?php

namespace FluxErp\Tests\Livewire\Portal\Ticket;

use FluxErp\Livewire\Portal\Ticket\Media;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class MediaTest extends TestCase
{
    protected string $livewireComponent = Media::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

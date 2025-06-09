<?php

namespace FluxErp\Tests\Livewire\Widgets\Settings\System;

use FluxErp\Livewire\Widgets\Settings\System\Queue;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class QueueTest extends TestCase
{
    protected string $livewireComponent = Queue::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

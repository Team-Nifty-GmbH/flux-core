<?php

namespace FluxErp\Tests\Livewire\Widgets\Settings\System;

use FluxErp\Livewire\Widgets\Settings\System\Scout;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ScoutTest extends TestCase
{
    protected string $livewireComponent = Scout::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

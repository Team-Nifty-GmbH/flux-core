<?php

namespace FluxErp\Tests\Livewire\Widgets\Settings\System;

use FluxErp\Livewire\Widgets\Settings\System\Extensions;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ExtensionsTest extends TestCase
{
    protected string $livewireComponent = Extensions::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

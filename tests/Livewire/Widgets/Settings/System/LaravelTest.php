<?php

namespace FluxErp\Tests\Livewire\Widgets\Settings\System;

use FluxErp\Livewire\Widgets\Settings\System\Laravel;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class LaravelTest extends TestCase
{
    protected string $livewireComponent = Laravel::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

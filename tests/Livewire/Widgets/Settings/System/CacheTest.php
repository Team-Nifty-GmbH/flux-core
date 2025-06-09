<?php

namespace FluxErp\Tests\Livewire\Widgets\Settings\System;

use FluxErp\Livewire\Widgets\Settings\System\Cache;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class CacheTest extends TestCase
{
    protected string $livewireComponent = Cache::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

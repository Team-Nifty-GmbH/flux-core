<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\System;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class SystemTest extends TestCase
{
    protected string $livewireComponent = System::class;

    public function test_renders_successfully(): void
    {
        Livewire::withoutLazyLoading()
            ->test($this->livewireComponent)
            ->assertStatus(200);
    }
}

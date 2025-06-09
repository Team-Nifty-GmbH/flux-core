<?php

namespace FluxErp\Tests\Livewire\Widgets\Settings\System;

use FluxErp\Livewire\Widgets\Settings\System\Php;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class PhpTest extends TestCase
{
    protected string $livewireComponent = Php::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

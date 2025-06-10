<?php

namespace FluxErp\Tests\Livewire\Widgets\Settings\System;

use FluxErp\Livewire\Widgets\Settings\System\Session;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class SessionTest extends TestCase
{
    protected string $livewireComponent = Session::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

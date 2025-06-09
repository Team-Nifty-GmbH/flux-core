<?php

namespace FluxErp\Tests\Livewire\Widgets\Settings\System;

use FluxErp\Livewire\Widgets\Settings\System\Server;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ServerTest extends TestCase
{
    protected string $livewireComponent = Server::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

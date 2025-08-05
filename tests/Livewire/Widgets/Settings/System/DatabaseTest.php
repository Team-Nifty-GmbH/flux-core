<?php

namespace FluxErp\Tests\Livewire\Widgets\Settings\System;

use FluxErp\Livewire\Widgets\Settings\System\Database;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class DatabaseTest extends TestCase
{
    protected string $livewireComponent = Database::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

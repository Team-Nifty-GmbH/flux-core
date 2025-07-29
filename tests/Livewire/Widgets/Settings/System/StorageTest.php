<?php

namespace FluxErp\Tests\Livewire\Widgets\Settings\System;

use FluxErp\Livewire\Widgets\Settings\System\Storage;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class StorageTest extends TestCase
{
    protected string $livewireComponent = Storage::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\RecordOrigins;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class RecordOriginsTest extends TestCase
{
    protected string $livewireComponent = RecordOrigins::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

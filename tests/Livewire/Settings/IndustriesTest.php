<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\Industries;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class IndustriesTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(Industries::class)
            ->assertStatus(200);
    }
}

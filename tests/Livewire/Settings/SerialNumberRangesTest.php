<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\SerialNumberRanges;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class SerialNumberRangesTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(SerialNumberRanges::class)
            ->assertStatus(200);
    }
}

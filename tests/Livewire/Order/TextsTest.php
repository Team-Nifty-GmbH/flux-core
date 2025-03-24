<?php

namespace FluxErp\Tests\Livewire\Order;

use FluxErp\Livewire\Order\Texts;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class TextsTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(Texts::class)
            ->assertStatus(200);
    }
}

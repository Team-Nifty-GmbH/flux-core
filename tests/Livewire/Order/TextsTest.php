<?php

namespace Tests\Feature\Livewire\Order;

use FluxErp\Livewire\Order\Texts;
use Livewire\Livewire;
use Tests\TestCase;

class TextsTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(Texts::class)
            ->assertStatus(200);
    }
}

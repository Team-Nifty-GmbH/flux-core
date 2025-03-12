<?php

namespace FluxErp\Tests\Livewire\Features\Communications;

use FluxErp\Livewire\Features\Communications\Communication;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class CommunicationTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(Communication::class)
            ->assertStatus(200);
    }
}

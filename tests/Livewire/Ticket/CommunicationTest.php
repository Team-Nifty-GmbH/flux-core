<?php

namespace FluxErp\Tests\Livewire\Ticket;

use FluxErp\Livewire\Ticket\Communication;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class CommunicationTest extends TestCase
{
    protected string $livewireComponent = Communication::class;

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

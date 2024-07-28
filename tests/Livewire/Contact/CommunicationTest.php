<?php

namespace FluxErp\Tests\Livewire\Contact;

use FluxErp\Livewire\Contact\Communication;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class CommunicationTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(Communication::class)
            ->assertStatus(200);
    }
}

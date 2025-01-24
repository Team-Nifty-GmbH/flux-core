<?php

namespace FluxErp\Tests\Livewire\Contact;

use FluxErp\Livewire\Contact\CommunicationList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class CommunicationListTest extends TestCase
{
    protected string $livewireComponent = CommunicationList::class;

    public function test_renders_successfully()
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}

<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\CommunicationList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class CommunicationListTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(CommunicationList::class)
            ->assertStatus(200);
    }
}

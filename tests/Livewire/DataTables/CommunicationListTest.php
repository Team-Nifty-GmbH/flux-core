<?php

namespace Tests\Feature\Livewire\DataTables;

use FluxErp\Livewire\DataTables\CommunicationList;
use Livewire\Livewire;
use Tests\TestCase;

class CommunicationListTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(CommunicationList::class)
            ->assertStatus(200);
    }
}

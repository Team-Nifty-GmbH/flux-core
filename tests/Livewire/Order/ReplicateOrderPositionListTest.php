<?php

namespace Tests\Feature\Livewire\Order;

use FluxErp\Livewire\Order\ReplicateOrderPositionList;
use Livewire\Livewire;
use Tests\TestCase;

class ReplicateOrderPositionListTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(ReplicateOrderPositionList::class)
            ->assertStatus(200);
    }
}

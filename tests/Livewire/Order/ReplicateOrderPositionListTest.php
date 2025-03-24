<?php

namespace FluxErp\Tests\Livewire\Order;

use FluxErp\Livewire\Order\ReplicateOrderPositionList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class ReplicateOrderPositionListTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(ReplicateOrderPositionList::class)
            ->assertStatus(200);
    }
}

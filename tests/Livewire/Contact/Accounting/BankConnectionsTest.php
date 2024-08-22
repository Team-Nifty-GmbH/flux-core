<?php

namespace FluxErp\Tests\Livewire\Contact\Accounting;

use FluxErp\Livewire\Contact\Accounting\BankConnections;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class BankConnectionsTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(BankConnections::class)
            ->assertStatus(200);
    }
}

<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\BankConnections;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class BankConnectionsTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(BankConnections::class)
            ->assertStatus(200);
    }
}

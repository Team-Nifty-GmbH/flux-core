<?php

namespace FluxErp\Tests\Livewire\Accounting;

use FluxErp\Livewire\Accounting\MoneyTransfer;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class MoneyTransferTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(MoneyTransfer::class)
            ->assertStatus(200);
    }
}

<?php

namespace Tests\Feature\Livewire\Accounting;

use FluxErp\Livewire\Accounting\MoneyTransfer;
use Livewire\Livewire;
use Tests\TestCase;

class MoneyTransferTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(MoneyTransfer::class)
            ->assertStatus(200);
    }
}

<?php

namespace Tests\Feature\Livewire\Accounting;

use FluxErp\Livewire\Accounting\DirectDebit;
use Livewire\Livewire;
use Tests\TestCase;

class DirectDebitTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(DirectDebit::class)
            ->assertStatus(200);
    }
}

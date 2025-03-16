<?php

namespace FluxErp\Tests\Livewire\Accounting;

use FluxErp\Livewire\Accounting\DirectDebit;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class DirectDebitTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(DirectDebit::class)
            ->assertStatus(200);
    }
}

<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\LedgerAccounts;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class LedgerAccountsTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(LedgerAccounts::class)
            ->assertStatus(200);
    }
}

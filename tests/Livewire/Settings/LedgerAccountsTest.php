<?php

namespace Tests\Feature\Livewire\Settings;

use FluxErp\Livewire\Settings\LedgerAccounts;
use Livewire\Livewire;
use Tests\TestCase;

class LedgerAccountsTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(LedgerAccounts::class)
            ->assertStatus(200);
    }
}

<?php

namespace Tests\Feature\Livewire\Settings;

use FluxErp\Livewire\Settings\MailAccounts;
use Livewire\Livewire;
use Tests\TestCase;

class MailAccountsTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(MailAccounts::class)
            ->assertStatus(200);
    }
}

<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\MailAccounts;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class MailAccountsTest extends TestCase
{
    public function test_renders_successfully(): void
    {
        Livewire::test(MailAccounts::class)
            ->assertStatus(200);
    }
}

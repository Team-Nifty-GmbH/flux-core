<?php

namespace FluxErp\Tests\Livewire\Contact;

use FluxErp\Livewire\Contact\Accounting;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class AccountingTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(Accounting::class)
            ->assertStatus(200);
    }
}

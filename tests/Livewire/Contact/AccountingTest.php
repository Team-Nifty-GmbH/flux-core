<?php

namespace Tests\Feature\Livewire\Contact;

use FluxErp\Livewire\Contact\Accounting;
use Livewire\Livewire;
use Tests\TestCase;

class AccountingTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(Accounting::class)
            ->assertStatus(200);
    }
}

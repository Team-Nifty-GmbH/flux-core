<?php

namespace Tests\Feature\Livewire\DataTables;

use FluxErp\Livewire\DataTables\MailAccountList;
use Livewire\Livewire;
use Tests\TestCase;

class MailAccountListTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(MailAccountList::class)
            ->assertStatus(200);
    }
}

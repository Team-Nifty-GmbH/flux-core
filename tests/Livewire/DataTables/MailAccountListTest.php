<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\MailAccountList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class MailAccountListTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(MailAccountList::class)
            ->assertStatus(200);
    }
}

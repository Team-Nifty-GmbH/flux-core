<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\MailFolderList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class MailFolderListTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(MailFolderList::class)
            ->assertStatus(200);
    }
}

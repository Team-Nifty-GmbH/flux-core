<?php

namespace Tests\Feature\Livewire\DataTables;

use FluxErp\Livewire\DataTables\MailFolderList;
use Livewire\Livewire;
use Tests\TestCase;

class MailFolderListTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(MailFolderList::class)
            ->assertStatus(200);
    }
}

<?php

use FluxErp\Livewire\DataTables\MailFolderList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(MailFolderList::class)
        ->assertOk();
});

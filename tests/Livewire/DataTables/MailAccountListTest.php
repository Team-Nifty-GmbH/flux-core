<?php

use FluxErp\Livewire\DataTables\MailAccountList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(MailAccountList::class)
        ->assertOk();
});

<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\DataTables\MailFolderList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(MailFolderList::class)
        ->assertStatus(200);
});

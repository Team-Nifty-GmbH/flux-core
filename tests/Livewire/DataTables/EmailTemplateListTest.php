<?php

use FluxErp\Livewire\DataTables\EmailTemplateList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(EmailTemplateList::class)
        ->assertOk();
});

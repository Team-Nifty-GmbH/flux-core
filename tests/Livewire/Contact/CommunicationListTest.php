<?php

use FluxErp\Livewire\Contact\CommunicationList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(CommunicationList::class)
        ->assertOk();
});

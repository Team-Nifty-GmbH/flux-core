<?php

use FluxErp\Livewire\Widgets\MyOverdueLeads;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(MyOverdueLeads::class)
        ->assertOk();
});

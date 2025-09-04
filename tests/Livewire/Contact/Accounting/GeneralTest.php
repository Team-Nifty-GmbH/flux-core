<?php

use FluxErp\Livewire\Contact\Accounting\General;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(General::class)
        ->assertOk();
});

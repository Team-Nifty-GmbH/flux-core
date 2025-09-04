<?php

use FluxErp\Livewire\Widgets\Settings\System\Session;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Session::class)
        ->assertOk();
});

<?php

use FluxErp\Livewire\Widgets\Settings\System\Server;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Server::class)
        ->assertOk();
});

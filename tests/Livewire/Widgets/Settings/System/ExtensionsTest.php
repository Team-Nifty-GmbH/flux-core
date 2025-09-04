<?php

use FluxErp\Livewire\Widgets\Settings\System\Extensions;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Extensions::class)
        ->assertOk();
});

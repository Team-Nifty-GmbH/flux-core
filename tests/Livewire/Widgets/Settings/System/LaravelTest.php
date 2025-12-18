<?php

use FluxErp\Livewire\Widgets\Settings\System\Laravel;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Laravel::class)
        ->assertOk();
});

<?php

use FluxErp\Livewire\Widgets\Settings\System\Scout;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Scout::class)
        ->assertOk();
});

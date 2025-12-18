<?php

use FluxErp\Livewire\Widgets\Settings\System\Cache;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Cache::class)
        ->assertOk();
});

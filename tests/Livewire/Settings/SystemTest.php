<?php

use FluxErp\Livewire\Settings\System;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::withoutLazyLoading()
        ->test(System::class)
        ->assertOk();
});

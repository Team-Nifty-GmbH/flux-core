<?php

use FluxErp\Livewire\Widgets\Settings\System\Storage;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Storage::class)
        ->assertOk();
});

<?php

use FluxErp\Livewire\Portal\SerialNumber\Media;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Media::class)
        ->assertOk();
});

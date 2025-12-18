<?php

use FluxErp\Livewire\Product\SerialNumber\Media;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Media::class)
        ->assertOk();
});

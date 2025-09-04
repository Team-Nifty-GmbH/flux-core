<?php

use FluxErp\Livewire\Media\Media;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Media::class)
        ->assertOk();
});

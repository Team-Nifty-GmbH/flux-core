<?php

use FluxErp\Livewire\Task\Media;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Media::class)
        ->assertOk();
});

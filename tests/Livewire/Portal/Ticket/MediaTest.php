<?php

use FluxErp\Livewire\Portal\Ticket\Media;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Media::class)
        ->assertOk();
});

<?php

use FluxErp\Livewire\Widgets\Generated\GeneratedValueBox;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(GeneratedValueBox::class)
        ->assertOk();
});

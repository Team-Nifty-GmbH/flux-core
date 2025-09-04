<?php

use FluxErp\Livewire\Settings\Tags;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Tags::class)
        ->assertOk();
});
